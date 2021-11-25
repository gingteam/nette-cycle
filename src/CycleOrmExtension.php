<?php

declare(strict_types=1);

namespace GingTeam\NetteCycle;

use Cycle\Bootstrap\Bootstrap;
use Cycle\Bootstrap\Config;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class CycleOrmExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'dsn' => Expect::string()->required(),
            'username' => Expect::string()->default(''),
            'password' => Expect::string()->default(''),
            'entityDirectory' => Expect::string()->required(),
            'cacheDirectory' => Expect::string()->nullable(),
            'logger' => Expect::anyOf(
                Expect::string(),
                Expect::type(Statement::class),
            )->nullable(),
        ]);
    }

    public function loadConfiguration()
    {
        /** @var \stdClass */
        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();

        $cycleConfig = $builder->addDefinition($this->prefix('config'))
            ->setFactory([Config::class, 'forDatabase'], [$config->dsn, $config->username, $config->password]);

        $cycleConfig->addSetup('? = ?->?(?)', [
            $cycleConfig,
            $cycleConfig,
            'withEntityDirectory',
            $config->entityDirectory,
        ]);

        if (null !== $config->cacheDirectory) {
            $cycleConfig->addSetup('? = ?->?(?)', [
                $cycleConfig,
                $cycleConfig,
                'withCacheDirectory',
                $config->cacheDirectory,
            ]);
        }

        if (null !== $config->logger) {
            $cycleConfig->addSetup('? = ?->?(?)', [
                $cycleConfig,
                $cycleConfig,
                'withLogger',
                $config->logger,
            ]);
        }

        $builder->addDefinition($this->prefix('orm'))
            ->setFactory([Bootstrap::class, 'fromConfig'], [$cycleConfig]);
    }
}
