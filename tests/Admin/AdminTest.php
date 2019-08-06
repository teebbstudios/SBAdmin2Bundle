<?php

namespace Teebb\SBAdmin2Bundle\Tests\Admin;

use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Teebb\SBAdmin2Bundle\Admin\BreadcrumbsBuilder;

class AdminTest extends KernelTestCase
{

    protected function setUp()
    {
        parent::setUp();

        self::bootKernel();

    }

    public function testArticleAdmin()
    {
        $articleAdmin = self::$container->get('App\Admin\ArticleAdmin');
        $em = self::$container->get('doctrine.orm.entity_manager');
        $this->assertTrue($articleAdmin->isChild());

        $articleAdmin->initialize();

        $this->assertSame('Article', $articleAdmin->getEntityClassLabel());

        $routes = $articleAdmin->getRoutes()->getElements();

        $article = $em->getRepository(Article::class)->find(12);

        var_dump($articleAdmin->generateObjectUrl('list', $article));
//        $this->assertArrayHasKey('create', $articleAdmin->getCrudConfigs());
//        $this->assertArrayHasKey('rest', $articleAdmin->getRest());
//
//        $breadcrumbsBuilder = new BreadcrumbsBuilder();
//
//        $breadcrumbs = $breadcrumbsBuilder->getBreadcrumbs($articleAdmin, 'list');

    }
}