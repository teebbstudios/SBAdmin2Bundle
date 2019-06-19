<?php


namespace Teebb\SBAdmin2Bundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class GlobalVariablesCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('twig')
            ->addMethodCall('addGlobal', ['teebb_sbadmin2', new Reference('teebb.sbadmin2.twig.global')]);
    }
}