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

use Teebb\SBAdmin2Bundle\Admin\AdminInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface RouteGeneratorInterface
{
    /**
     * @param string $name
     * @param bool   $absolute
     *
     * @return string
     */
    public function generateUrl(AdminInterface $admin, $name, array $parameters = [], $absolute = false);

    /**
     * @param string $name
     * @param bool   $absolute
     *
     * @return array
     */
    public function generateMenuUrl(AdminInterface $admin, $name, array $parameters = [], $absolute = false);

    /**
     * @param string $name
     * @param bool   $absolute
     *
     * @return string
     */
    public function generate($name, array $parameters = [], $absolute = false);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasAdminRoute(AdminInterface $admin, $name);
}
