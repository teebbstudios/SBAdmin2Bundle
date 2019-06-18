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

        $rootNode
            ->children()
                ->scalarNode('site_name')->defaultValue('TEEBB SBAdmin2')->cannotBeEmpty()->end()
                ->scalarNode('site_logo')->defaultValue('bundles/teebbsbadmin2/img/logo.png')->cannotBeEmpty()->end()
                ->scalarNode('favicon')->defaultValue('bundles/teebbsbadmin2/img/favicon.ico')->cannotBeEmpty()->end()

                ->arrayNode("options")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('search')->defaultValue(true)->info('Enable/Disable the top bar search form.')->end()
                        ->booleanNode('alert')->defaultValue(true)->info('Enable/Disable the top bar alert list.')->end()
                        ->booleanNode('messages')->defaultValue(false)->info('Enable/Disable the top bar messages list.')->end()
                        ->enumNode('logo_mode')
                            ->values(['single_image', 'single_text', 'both'])
                            ->defaultValue('both')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('default_label_catalogue')
                            ->defaultValue('TeebbSBAdmin2Bundle')
                            ->info("Label Catalogue used for admin services if one isn't provided.")
                        ->end()
                        ->scalarNode('default_icon')
                            ->defaultValue('<i class="fas fa-folder"></i>')
                            ->info("Icon used for admin services if one isn't provided.")
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('dashboard')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('groups')->info('The side menu groups.')
                            ->useAttributeAsKey('group_name')
                            ->arrayPrototype()
                                ->useAttributeAsKey('item_name')
                                ->arrayPrototype()
                                    ->beforeNormalization()
                                        ->ifArray()
                                        ->then(function ($items) {
                                            if (isset($items['provider'])) {
                                                $disallowedItems = ['items', 'label'];
                                                foreach ($disallowedItems as $item) {
                                                    if (isset($items[$item])) {
                                                        throw new \InvalidArgumentException(sprintf('The config value "%s" cannot be used alongside "provider" config value', $item));
                                                    }
                                                }
                                            }

                                            return $items;
                                        })
                                    ->end()

                                    ->children()
                                        ->scalarNode('label')->end()
                                        ->scalarNode('label_catalogue')->end()
                                        ->scalarNode('icon')->defaultValue('<i class="fas fa-folder"></i>')->end()
                                        ->scalarNode('provider')->end()
                                        ->arrayNode('items')
                                            ->beforeNormalization()
                                                ->ifArray()
                                                ->then(function ($items) {
                                                    foreach ($items as $key => $item) {
                                                        if (\is_array($item)) {
                                                            if (!\array_key_exists('label', $item) || !\array_key_exists('route', $item)) {
                                                                throw new \InvalidArgumentException('Expected either parameters "route" and "label" for array items');
                                                            }

                                                            if (!\array_key_exists('route_params', $item)) {
                                                                $items[$key]['route_params'] = [];
                                                            }

                                                            $items[$key]['admin'] = '';
                                                        } else {
                                                            $items[$key] = [
                                                                'admin' => $item,
                                                                'label' => '',
                                                                'route' => '',
                                                                'route_params' => [],
                                                                'route_absolute' => false,
                                                            ];
                                                        }
                                                    }

                                                    return $items;
                                                })
                                            ->end()
                                            ->arrayPrototype()
                                                ->children()
                                                    ->scalarNode('admin')->end()
                                                    ->scalarNode('label')->end()
                                                    ->scalarNode('route')->end()
                                                    ->arrayNode('roles')
                                                        ->prototype('scalar')
                                                            ->info('Roles which will see the route in the menu.')
                                                            ->defaultValue([])
                                                        ->end()
                                                    ->end()
                                                    ->arrayNode('route_params')->prototype('scalar')->end()->end()
                                                    ->booleanNode('route_absolute')
                                                        ->info('Whether the generated url should be absolute')->defaultFalse()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('roles')->info('Roles which will see the route in the menu group.')
                                            ->prototype('scalar')->defaultValue([])->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('blocks')
                            ->defaultValue([[
                                'position' => 'left',
                                'settings' => [],
                                'type' => '',
                                'roles' => [],
                            ]])
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('type')->cannotBeEmpty()->end()
                                    ->arrayNode('roles')
                                        ->defaultValue([])
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('settings')
                                        ->useAttributeAsKey('id')
                                        ->prototype('variable')->defaultValue([])->end()
                                    ->end()
                                    ->scalarNode('position')->defaultValue('left')->end()
                                    ->scalarNode('class')->defaultValue('col-md-6')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('assets')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('stylesheets')
                            ->defaultValue([
                                'bundles/teebbsbadmin2/vendor/fontawesome-free/css/all.min.css',

                                'bundles/teebbsbadmin2/css/sb-admin-2.min.css',
                            ])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('extra_stylesheets')->info('stylesheets to add to the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('remove_stylesheets')->info('stylesheets to remove from the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('javascripts')
                            ->defaultValue([
                                'bundles/teebbsbadmin2/vendor/jquery/jquery.min.js',
                                'bundles/teebbsbadmin2/vendor/bootstrap/js/bootstrap.bundle.min.js',
                                'bundles/teebbsbadmin2/vendor/jquery-easing/jquery.easing.min.js',


                                'bundles/teebbsbadmin2/js/sb-admin-2.min.js',
                            ])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('extra_javascripts')->info('javascripts to add to the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('remove_javascripts')->info('javascripts to remove from the page')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode("design")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('sidebar_background_class')
                            ->values(['bg-gradient-primary', 'bg-gradient-secondary', 'bg-gradient-dark', 'bg-gradient-danger', 'bg-gradient-warning', 'bg-gradient-info',])
                            ->defaultValue('bg-gradient-primary')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()


            ->end();
        return $treeBuilder;
    }
}