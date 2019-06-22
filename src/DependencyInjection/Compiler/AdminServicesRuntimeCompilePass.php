<?php

namespace Teebb\SBAdmin2Bundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AdminServicesRuntimeCompilePass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $groupDefaults = $adminServiceIds = $adminClasses = [];


    }
}