<?php

namespace Teebb\SBAdmin2Bundle\Controller;


use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Teebb\SBAdmin2Bundle\Admin\AdminInterface;
use Teebb\SBAdmin2Bundle\Config\TeebbSBAdmin2ConfigInterface;
use Teebb\SBAdmin2Bundle\Templating\TemplateRegistryInterface;

class SearchController extends CRUDController
{
    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @var TeebbSBAdmin2ConfigInterface
     */
    protected $sbadmin2Config;

    /**
     * @var TemplateRegistryInterface
     */
    protected $templateRegistry;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    public function __construct(TeebbSBAdmin2ConfigInterface $sbadmin2Config, TemplateRegistryInterface $templateRegistry)
    {
        $this->sbadmin2Config = $sbadmin2Config;

        $this->templateRegistry = $templateRegistry;
    }

    public function __invoke(Request $request)
    {
        $adminId = $this->sbadmin2Config->getOption('search')['admin'];

        $properties = $this->sbadmin2Config->getOption('search')['property'];

        $this->admin = $this->container->get($adminId);

        $this->paginator = $this->container->get('knp_paginator');

        if (!$this->isCsrfTokenValid('teebb.search', $request->request->get('_csrf_token')))
        {
            throw new InvalidCsrfTokenException('Search token is Invalid. CSRF attack? I Found You!');
        }else{
            $keywords = $request->get('keywords');

            $filterArray = [];
            foreach ($properties as $property)
            {
                $filterArray[$property] = $keywords;
            }

            $request->request->set('filter', $filterArray);

            return $this->listAction($request);
        }
    }

}