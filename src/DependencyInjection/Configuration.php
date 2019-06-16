<?php


namespace Teebb\SBAdmin2Bundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        if (\method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('teebb_sbadmin2');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('teebb_sbadmin2');
        }


        return $treeBuilder;
    }
}