<?php

namespace Teebb\SBAdmin2Bundle\Tests\Admin;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Teebb\SBAdmin2Bundle\Admin\BreadcrumbsBuilder;

class AdminTest extends KernelTestCase
{

    protected function setUp()
    {
        parent::setUp();

        self::bootKernel();

    }

    public function testAdmins()
    {
        $articleAdmin = self::$container->get('App\Admin\ArticleAdmin');
        var_dump($articleAdmin->getRoutes()->getElements());

        $articleAdmin->initialize();

        $this->assertSame('Article', $articleAdmin->getEntityClassLabel());

        $routes = $articleAdmin->getRoutes()->getElements();

        $this->assertArrayHasKey('create', $articleAdmin->getCrudConfigs());
        $this->assertArrayHasKey('rest', $articleAdmin->getRest());

        $breadcrumbsBuilder = new BreadcrumbsBuilder();

        $breadcrumbs = $breadcrumbsBuilder->getBreadcrumbs($articleAdmin, 'list');

        var_dump($breadcrumbs);
    }
}