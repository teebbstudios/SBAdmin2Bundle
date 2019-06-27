<?php

namespace Teebb\SBAdmin2Bundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Teebb\SBAdmin2Bundle\Controller\CRUDController;

class AdminServicesCompilePass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {

        $configAdminGroups = $adminServiceIds = $entityClasses = [];

        $configAdmins = $container->getParameter('teebb.sbadmin2.configuration.admins');

        $dashboardGroupsSettings = $container->getParameter('teebb.sbadmin2.configuration.dashboard_groups');

        foreach ($configAdmins as $adminServiceId => $adminConfig) {

            $adminServiceIds[] = $adminServiceId;

            $entityClasses[$adminConfig['entity']] = $adminServiceId;

            $definition = $container->getDefinition($adminServiceId);
            $arguments = [
                0 => $adminServiceId,
                1 => $adminConfig['entity'],
                2 => $adminConfig['controller'] ?? CRUDController::class,
            ];

            $definition->setArguments($arguments);

            if (array_key_exists('parent', $adminConfig) && array_key_exists('map_property', $adminConfig)) {
                $definition->addMethodCall('addChild', [new Reference($adminConfig['parent']), $adminConfig['map_property']]);
            }

            $labelCatalogue = $adminConfig['label_catalogue'] ??
                $container->getParameter('teebb.sbadmin2.configuration.default_label_catalogue');

            $groupIcon = $adminConfig['icon'] ??
                $container->getParameter('teebb.sbadmin2.configuration.default_icon');


            if (!isset($configAdminGroups[$adminConfig['group']][$adminConfig['label']])) {
                $configAdminGroups[$adminConfig['group']][$adminConfig['label']] = [
                    'label' => $adminConfig['label'],
                    'label_catalogue' => $labelCatalogue,
                    'icon' => $groupIcon,
                    'roles' => [],
                    'priority' => $adminConfig['priority'],
                ];

                $configAdminGroups[$adminConfig['group']][$adminConfig['label']]['items'][] = [
                    'admin' => $adminServiceId,
                    'label' => $adminConfig['label'] ?? '',
                    'route' => '',
                    'route_params' => [],
                    'route_absolute' => false,
                ];
            }

        }

        $groups = array_merge_recursive($dashboardGroupsSettings, $configAdminGroups);

        $elementSort = function (&$element) {
            uasort(
                $element,
                function ($a, $b) {

                    $a = !empty($a['priority']) ? $a['priority'] : 0;
                    $b = !empty($b['priority']) ? $b['priority'] : 0;

                    if ($a === $b) {
                        return 0;
                    }

                    return $a < $b ? -1 : 1;
                }
            );
        };

        array_walk($groups, $elementSort);


        $sbadmin2ConfigDefinition = $container->getDefinition('teebb.sbadmin2.config');

        $sbadmin2ConfigDefinition->addMethodCall('setAdminGroups', [$groups]);
        $sbadmin2ConfigDefinition->addMethodCall('setAdminServiceIds', [$adminServiceIds]);
        $sbadmin2ConfigDefinition->addMethodCall('setEntityClasses', [$entityClasses]);

        $templates = $container->getParameter('teebb.sbadmin2.configuration.templates');
        $sbadmin2ConfigDefinition->addMethodCall('setTemplates', [$templates]);

    }
}