<?php

namespace Teebb\SBAdmin2Bundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
            'command',
            'core',
        ];

        foreach ($resources as $resource) {
            $loader->load($resource.'.xml');
        }

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $config['options']['javascripts'] = $this->buildJavascripts($config);
        $config['options']['stylesheets'] = $this->buildStylesheets($config);

        $teebbSBAdmin2Config = $container->getDefinition('teebb.sbadmin2.config');
        $teebbSBAdmin2Config->replaceArgument(0, $config['site_name']);
        $teebbSBAdmin2Config->replaceArgument(1, $config['site_logo']);
        $teebbSBAdmin2Config->replaceArgument(2, $config['favicon']);
        $teebbSBAdmin2Config->replaceArgument(3, $config['options']);


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