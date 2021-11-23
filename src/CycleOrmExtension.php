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
            'entityDir' => Expect::string()->required(),
            'cache' => Expect::string()->nullable(),
            'logger' => Expect::type(LoggerInterface::class)->nullable(),
        ]);
    }

    public function loadConfiguration()
    {
        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();

        $cycleConfig = $builder->addDefinition($this->prefix('config'))
            ->setFactory([Config::class, 'forDatabase'], [$config->dsn, $config->username, $config->password])
            ->setAutowired(false);

        if (null !== $config->cache) {
            $cycleConfig = $builder->addDefinition($this->prefix('cache'))
                ->setFactory([$cycleConfig, 'withCacheDirectory'], [$config->cache])
                ->setAutowired(false);
        }

        if (null !== $config->logger) {
            $cycleConfig = $builder->addDefinition($this->prefix('logger'))
                ->setFactory([$cycleConfig, 'withLogger'], [$config->logger])
                ->setAutowired(false);
        }

        $cycleConfig = $builder->addDefinition($this->prefix('entity'))
            ->setFactory([$cycleConfig, 'withEntityDirectory'], [$config->entityDir]);

        $builder->addDefinition($this->prefix('orm'))
            ->setFactory([Bootstrap::class, 'fromConfig'], [$cycleConfig]);
    }
}
