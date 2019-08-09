<?php

namespace Teebb\SBAdmin2Bundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Teebb\SBAdmin2Bundle\Admin\AdminInterface;

class TeebbSBAdmin2Extension extends Extension
{
    /**
     * @param array $configs   The configurations being loaded
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $resources = [
            'block',
            'command',
            'controller',
            'core',
            'form',
            'menu',
            'route',
            'security',
            'twig',
        ];

        foreach ($resources as $resource) {
            $loader->load($resource.'.xml');
        }

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $config['options']['javascripts'] = $this->buildJavascripts($config);
        $config['options']['stylesheets'] = $this->buildStylesheets($config);
        $config['options']['design'] = $config['design'];
        $config['options']['dashboard'] = $config['dashboard']['heading'];

        $teebbSBAdmin2Config = $container->getDefinition('teebb.sbadmin2.config');
        $teebbSBAdmin2Config->replaceArgument(1, $config['logo_text']);
        $teebbSBAdmin2Config->replaceArgument(2, $config['logo_image']);
        $teebbSBAdmin2Config->replaceArgument(3, $config['favicon']);
        $teebbSBAdmin2Config->replaceArgument(4, $config['options']);

        $container->setParameter('teebb.sbadmin2.configuration.default_label_catalogue', $config['options']['default_label_catalogue']);

        $container->setParameter('teebb.sbadmin2.configuration.default_icon', $config['options']['default_icon']);

        $container->setParameter('teebb.sbadmin2.configuration.dashboard_groups', $config['dashboard']['groups']);

        $container->setParameter('teebb.sbadmin2.configuration.dashboard_blocks', $config['dashboard']['blocks']);

        $container->setParameter('teebb.sbadmin2.configuration.templates', $config['templates']);

        $container->setParameter('teebb.sbadmin2.configuration.admins', $config['admins']);

        $container->setParameter('teebb.sbadmin2.configuration.security', $config['security']);

        $container->setParameter('teebb.admin.configuration.security.role_super_admin', $config['security']['role_super_admin']);
    }

    private function buildStylesheets($config): array
    {
        return $this->mergeArray(
            $config['assets']['stylesheets'],
            $config['assets']['extra_stylesheets'],
            $config['assets']['remove_stylesheets']
        );
    }

    private function buildJavascripts($config): array
    {
        return $this->mergeArray(
            $config['assets']['javascripts'],
            $config['assets']['extra_javascripts'],
            $config['assets']['remove_javascripts']
        );
    }

    private function mergeArray(array $array, array $addArray, array $removeArray = []): array
    {
        foreach ($addArray as $toAdd) {
            array_push($array, $toAdd);
        }
        foreach ($removeArray as $toRemove) {
            if (\in_array($toRemove, $array, true)) {
                array_splice($array, array_search($toRemove, $array, true), 1);
            }
        }

        return $array;
    }

}