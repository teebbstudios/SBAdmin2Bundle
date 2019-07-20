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

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Teebb\SBAdmin2Bundle\Config\TeebbSBAdmin2ConfigInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RoutesCacheWarmUp implements CacheWarmerInterface
{
    /**
     * @var RoutesCache
     */
    protected $cache;

    /**
     * @var TeebbSBAdmin2ConfigInterface
     */
    protected $config;

    public function __construct(RoutesCache $cache, TeebbSBAdmin2ConfigInterface $config)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    public function isOptional()
    {
        return true;
    }

    public function warmUp($cacheDir)
    {
        foreach ($this->config->getAdminServiceIds() as $id) {
            $this->cache->load($this->config->getInstance($id));
        }
    }
}
