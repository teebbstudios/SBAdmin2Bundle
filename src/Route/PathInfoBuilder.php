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
class PathInfoBuilder implements RouteBuilderInterface
{

    public function build(AdminInterface $admin, RouteCollection $collection)
    {
        $collection->add('list');
        $collection->add('create');
        $collection->add('batch');
        $collection->add('edit', $admin->getRouterIdParameter().'/edit');
        $collection->add('delete', $admin->getRouterIdParameter().'/delete');
        $collection->add('show', $admin->getRouterIdParameter().'/show');

        // add children urls
        /** @var AdminInterface $children */
        foreach ($admin->getChildren() as $children) {
            $collection->addCollection($children->getRoutes());
        }
    }
}
