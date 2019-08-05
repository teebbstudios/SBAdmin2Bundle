<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teebb\SBAdmin2Bundle\Route;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Teebb\SBAdmin2Bundle\Admin\AdminInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RoutesCache
{
    /**
     * @var string
     */
    protected $cacheFolder;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @param string $cacheFolder
     * @param bool   $debug
     */
    public function __construct($cacheFolder, $debug)
    {
        $this->cacheFolder = $cacheFolder;
        $this->debug = $debug;
    }

    /**
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function load(AdminInterface $admin)
    {
        $filename = $this->cacheFolder.'/route_'.md5($admin->getAdminServiceId());

        $cache = new ConfigCache($filename, $this->debug);

        if (!$cache->isFresh()) {
            $resources = [];
            $routes = [];

            $reflection = new \ReflectionObject($admin);
            if (file_exists($reflection->getFileName())) {
                $resources[] = new FileResource($reflection->getFileName());
            }

            if (!$admin->getRoutes()) {
                throw new \RuntimeException('Invalid data type, AdminInterface::getRoutes must return a RouteCollection');
            }

            foreach ($admin->getRoutes()->getElements() as $code => $route) {
                var_dump($code);
                $routes[$code] = $route->getDefault('_teebb_name');
            }

            $cache->write(serialize($routes), $resources);
        }

        return unserialize(file_get_contents($filename));
    }
}
