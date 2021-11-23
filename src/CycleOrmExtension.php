<?php

declare(strict_types=1);

namespace GingTeam\NetteCycle;

use Cycle\Bootstrap\Bootstrap;
use Cycle\Bootstrap\Config;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Psr\Log\LoggerInterface;

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
            'logger' => Expect::type(LoggerInterface::class)->nullable(),
        ]);
    }

    public function loadConfiguration()
    {
        /** @var \stdClass */
        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();

        $cycleConfig = Config::forDatabase($config->dsn, $config->username, $config->password);
        $cycleConfig = $cycleConfig->withEntityDirectory($config->entityDirectory);

        if (null !== $config->cacheDirectory) {
            $cycleConfig = $cycleConfig->withCacheDirectory($config->cacheDirectory);
        }

        if (null !== $config->logger) {
            $cycleConfig = $cycleConfig->withLogger($config->logger);
        }

        $builder->addDefinition($this->prefix('orm'))
            ->setFactory([Bootstrap::class, 'fromConfig'], [$cycleConfig]);
    }
}
