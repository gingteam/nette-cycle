<?php

use Cycle\ORM\ORMInterface;
use GingTeam\NetteCycle\CycleOrmExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use PHPUnit\Framework\TestCase;

test('extension', function () {
    $loader = new ContainerLoader(__DIR__.'/temp', autoRebuild: true);

    $class = $loader->load(function (Compiler $compiler) {
        $compiler->addConfig([
            'parameters' => ['root' => __DIR__],
        ]);
        $compiler->addExtension('cycle', new CycleOrmExtension());
        $compiler->loadConfig(__DIR__.'/cycle.neon');
    });

    /** @var Container */
    $container = new $class();

    /* @var TestCase $this */
    $this->assertInstanceOf(ORMInterface::class, $container->getByType(ORMInterface::class));
});
