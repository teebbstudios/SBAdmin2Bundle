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
        ];

        foreach ($resources as $resource) {
            $loader->load($resource.'.xml');
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

    }
}