<?php


namespace Teebb\SBAdmin2Bundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
                ->scalarNode('logo_text')->defaultValue('TEEBB SBAdmin2')->cannotBeEmpty()->end()
                ->scalarNode('logo_image')->defaultValue('bundles/teebbsbadmin2/img/logo.png')->cannotBeEmpty()->end()
                ->scalarNode('favicon')->defaultValue('bundles/teebbsbadmin2/img/favicon.ico')->cannotBeEmpty()->end()
            ->end();

        $this->addSecuritySection($rootNode);

        $this->addAdminsConfigSection($rootNode);

        $this->addDashboardSection($rootNode);

        $this->addDesignSection($rootNode);

        $this->addTemplatesSection($rootNode);
        $this->addAssetsSection($rootNode);

        $this->addOptionsSection($rootNode);

        $this->addAdminsSection($rootNode);

        return $treeBuilder;
    }

    private function addDesignSection(ArrayNodeDefinition $rootNode){
        $rootNode
            ->children()
                ->arrayNode('design')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('sidebar_bg_class')->info('Left side background class.')
                            ->defaultValue('bg-gradient-primary')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addSecuritySection(ArrayNodeDefinition $rootNode){
        $rootNode
            ->children()
                ->arrayNode('security')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('handler')->info('The admin security handler.')
                            ->defaultValue('teebb.sbadmin2.security.handler.noop')
                        ->end()
                        ->scalarNode('role_super_admin')->info('The super admin role.')
                            ->defaultValue('ROLE_SUPER_ADMIN')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addAdminsConfigSection(ArrayNodeDefinition $rootNode){
        $rootNode
            ->children()
                ->arrayNode('admin_configs')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('translator_strategy')->info('The admin label translator strategy service id.')
                            ->defaultValue('teebb.sbadmin2.label.strategy.native')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addTemplatesSection(ArrayNodeDefinition $rootNode){
        $rootNode
            ->children()
                ->arrayNode('templates')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('layout')->defaultValue('@TeebbSBAdmin2/standard_layout.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('dashboard')->defaultValue('@TeebbSBAdmin2/Core/dashboard.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('knp_sidebar_menu')->defaultValue('@TeebbSBAdmin2/Menu/teebb_menu.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('base_list')->defaultValue('@TeebbSBAdmin2/CRUD/base_list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('base_edit')->defaultValue('@TeebbSBAdmin2/CRUD/base_edit.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('base_create')->defaultValue('@TeebbSBAdmin2/CRUD/base_create.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('simple_list')->defaultValue('@TeebbSBAdmin2/CRUD/simple_list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('full_list')->defaultValue('@TeebbSBAdmin2/CRUD/full_list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('full_list_table')->defaultValue('@TeebbSBAdmin2/CRUD/full_list_table.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('tab_simple_list')->defaultValue('@TeebbSBAdmin2/CRUD/tab_simple_list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('edit_form')->defaultValue('@TeebbSBAdmin2/CRUD/edit_form.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('create_form')->defaultValue('@TeebbSBAdmin2/CRUD/create_form.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('bootstrap4_pager')->defaultValue('@TeebbSBAdmin2/Pager/twitter_bootstrap_v4_pagination.html.twig')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addAssetsSection(ArrayNodeDefinition $rootNode){
        $rootNode
            ->children()
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
            ->end()
        ;
    }

    private function addOptionsSection(ArrayNodeDefinition $rootNode){
        $rootNode
            ->children()
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
                            ->defaultValue('fa-folder')
                            ->info("Icon used for admin services if one isn't provided.")
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addDashboardSection(ArrayNodeDefinition $rootNode){
        $rootNode
            ->children()
                ->arrayNode('dashboard')
                    ->addDefaultsIfNotSet()
                    ->children()

                        ->arrayNode('heading')->info('The dashboard page content heading title and link.')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('label')->defaultValue("Dashboard")->cannotBeEmpty()->end()
                                ->arrayNode('link')
                                    ->children()
                                        ->scalarNode('link_route')->end()
                                        ->scalarNode('link_title')->end()
                                        ->scalarNode('link_icon')->defaultValue('fa-plus-circle')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

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
                                    ->scalarNode('icon')->defaultValue('fa-folder')->end()
                                    ->integerNode('priority')->defaultValue(0)->end()
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

                                                        $items[$key]['admin'] = null;
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
            ->end()
        ;
    }

    private function addAdminsSection(ArrayNodeDefinition $rootNode){
        $rootNode
            ->children()
                ->arrayNode("admins")->info('Admins config.')
                ->useAttributeAsKey('service_id')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('entity')->cannotBeEmpty()->end()
                            ->scalarNode('controller')->end()
                            ->scalarNode('group')->defaultValue('default')->end()
                            ->scalarNode('label')->cannotBeEmpty()->end()
                            ->booleanNode('hide_sidebar')->info('Whether to display in the sidebar menu.')->defaultFalse()->end()
                            ->scalarNode('icon')->defaultValue('fa-folder')->end()
                            ->integerNode('priority')->defaultValue(0)->end()
                            ->arrayNode('roles')
                                ->prototype('scalar')->defaultValue([])->end()
                            ->end()

                            ->scalarNode('children')->cannotBeEmpty()->info('The admin children admin for general route.')->end()
                            ->scalarNode('map_property')->cannotBeEmpty()->end()

                            ->scalarNode('label_catalogue')->defaultValue("TeebbSBAdmin2Bundle")->info('Current admin translation domain catelogue.')->end()
                            ->scalarNode('title')->info('Content heading title and title syntax value. Default will auto generate.')->end()
                            ->arrayNode('head_link')
                                ->children()
                                    ->scalarNode('link_route')->end()
                                    ->scalarNode('link_title')->end()
                                    ->scalarNode('link_icon')->defaultValue('fa-plus-circle')->end()
                                ->end()
                            ->end()

                            ->arrayNode('rest')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('group')->end()
                                        ->arrayNode('roles')
                                            ->prototype('scalar')->defaultValue([])->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()

                            ->arrayNode('form')
                                ->children()
                                    ->arrayNode('fields')->defaultValue([])->info('The create edit form fields.')
                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('property')->info('The property')->end()
                                                ->scalarNode('label')->info('The label')->end()
                                                ->scalarNode('type')->info('The property form type. Default guess field type.')->end()
                                                ->arrayNode('options')->info('This form field options. See symfony form doc.')
                                                    ->prototype('variable')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()

                            ->arrayNode('create')
                                ->children()
                                    ->arrayNode('permission')
                                        ->children()
                                            ->scalarNode('name')->info('Permission Name')->end()
                                            ->scalarNode('description')->end()
                                            ->arrayNode('roles')->info('The roles have this permission.')
                                                ->scalarPrototype()->defaultValue([])->end()
                                            ->end()
                                        ->end()
                                    ->end()

                                    ->arrayNode('fields')->defaultValue([])->info('The edit form fields.')
                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('property')->info('The property')->end()
                                                ->scalarNode('label')->info('The label')->end()
                                                ->scalarNode('type')->info('The property form type. Default guess field type.')->end()
                                                ->arrayNode('options')->info('This form field options. See symfony form doc.')
                                                    ->prototype('variable')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()

                                ->end()
                            ->end()

                            ->arrayNode('edit')
                                ->children()
                                    ->arrayNode('permission')
                                        ->children()
                                            ->scalarNode('name')->info('Permission Name')->end()
                                            ->scalarNode('description')->end()
                                            ->arrayNode('roles')->info('The roles have this permission.')
                                                ->scalarPrototype()->defaultValue([])->end()
                                            ->end()
                                        ->end()
                                    ->end()

                                    ->arrayNode('fields')->defaultValue([])->info('The edit form fields.')
                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('property')->info('The property')->end()
                                                ->scalarNode('label')->info('The label')->end()
                                                ->scalarNode('type')->info('The property form type. Default guess field type.')->end()
                                                ->arrayNode('options')->info('This form field options. See symfony form doc.')
                                                    ->prototype('variable')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()

                                ->end()
                            ->end()

                            ->arrayNode('delete')
                                ->children()
                                    ->arrayNode('permission')
                                        ->children()
                                            ->scalarNode('name')->info('Permission Name')->end()
                                            ->scalarNode('description')->end()
                                            ->arrayNode('roles')->info('The roles have this permission.')
                                                ->scalarPrototype()->defaultValue([])->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()

                            ->arrayNode('list')->info('Entity list config.')
                                ->children()
                                    ->arrayNode('permission')
                                        ->children()
                                            ->scalarNode('name')->info('Permission Name')->end()
                                            ->scalarNode('description')->end()
                                            ->arrayNode('roles')->info('The roles have this permission.')
                                                ->scalarPrototype()
                                                    ->defaultValue([])
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()

                                    ->arrayNode('fields')->info('Config the entity field to show in the list.')
                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('property')->end()
                                                ->scalarNode('label')->end()
                                                ->enumNode('action')->values(['edit','show'])->info('This field href link.')->end()
                                                ->scalarNode('class')->defaultValue('')->info('This column css class.')->end()
                                                ->booleanNode('sortable')->defaultFalse()->info('This column head can be sortable.')->end()
                                            ->end()
                                        ->end()
                                    ->end()

                                    ->arrayNode('actions')->info('List item action.')
                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('name')->end()
                                                ->scalarNode('label')->end()
                                                ->scalarNode('icon')->defaultValue('')->end()
                                                ->scalarNode('class')->defaultValue('')->end()
                                                ->enumNode('type')->defaultValue('item')->values(['item','group'])->info('The action button style. item: single button, group: button with font icon.')->end()
                                                ->arrayNode('roles')
                                                    ->scalarPrototype()
                                                        ->defaultValue([])
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()

                                    ->arrayNode('filters')->info('Filter the list item.')
                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('property')->info('The property')->end()
                                                ->scalarNode('label')->info('The label')->end()
                                                ->scalarNode('type')->info('The property form field type.Default guess field type.')->end()
                                                ->arrayNode('options')->info('This form field options. See symfony form doc.')
                                                    ->prototype('variable')->end()
                                                ->end()
                                                ->arrayNode('roles')
                                                    ->scalarPrototype()
                                                        ->defaultValue([])
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()

                                    ->arrayNode('batch_actions')->info('batch option for the list items.')
                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('action')->info('The batch option name.')->end()
                                                ->scalarNode('option_name')->info('The option syntax name.')->end()
                                                ->scalarNode('option_label')->info('The option syntax value.')->end()
                                                ->arrayNode('roles')
                                                    ->scalarPrototype()
                                                        ->defaultValue([])
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()

                                ->end()
                            ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}