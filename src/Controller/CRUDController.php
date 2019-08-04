<?php

namespace Teebb\SBAdmin2Bundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Teebb\SBAdmin2Bundle\Admin\AdminInterface;
use Teebb\SBAdmin2Bundle\Templating\TemplateRegistryInterface;

class CRUDController extends AbstractFOSRestController
{
    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @var TemplateRegistryInterface
     */
    protected $templateRegistry;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }


    public function configure()
    {
        $request = $this->getRequest();

        $adminCode = $request->get('_teebb_admin');

        if (!$adminCode) {
            throw new \RuntimeException(sprintf(
                'There is no `_teebb_admin` defined for the controller `%s` and the current route `%s`',
                \get_class($this),
                $request->get('_route')
            ));
        }

        $this->admin = $this->container->get('teebb.sbadmin2.config')->getAdminByAdminCode($adminCode);

        if (!$this->admin) {
            throw new \RuntimeException(sprintf(
                'Unable to find the admin class related to the current controller (%s)',
                \get_class($this)
            ));
        }

        $this->templateRegistry = $this->container->get('teebb.sbadmin2.template_registry');

        $rootAdmin = $this->admin;

        while ($rootAdmin->isChild()) {
            $rootAdmin->setBoolCurrentChild(true);
            $rootAdmin = $rootAdmin->getParent();
        }

        $rootAdmin->setRequest($request);

        //Set PaginatorInterface
        $this->paginator = $this->container->get('knp_paginator');
    }

    public function listAction(Request $request)
    {
        $this->admin->checkAccess('list');

        $listItemActions = $this->admin->getListItemActions();
        $listItemProperties = $this->admin->getListProperties();
        $filterProperties = $this->admin->getAccessFilterProperties();

        $filterParameters = $request->get('filter');

        $pagination = $this->paginator->paginate(
            empty($filterParameters) ? $this->admin->getResults() : $this->admin->getConditionalQueryResults($filterParameters),
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('limit', 10)/*page number*/
        );

        $filterForm = $this->admin->getListFilterForm();

        $templateName = $filterForm == null ? 'simple_list' : 'full_list';

        if ($filterForm !== null)
        {
            $filterForm->handleRequest($request);
            if ($filterForm->isSubmitted() && $filterForm->isValid()) {

                $filterParameters = $filterForm->getData();

                $pagination = $this->paginator->paginate(
                    $this->admin->getConditionalQueryResults($filterParameters),
                    $request->query->getInt('page', 1)/*page number*/,
                    $request->query->getInt('limit', 10)/*page number*/
                );

            }
        }

        return $this->render($this->templateRegistry->getTemplate($templateName), [
                'filterProperties' => $filterProperties,
                'filterForm' => $filterForm == null ? null : $filterForm->createView(),
                'pagination' => $pagination,
                'action' => 'List',
                'admin' => $this->admin,
                'list_item_actions' => $listItemActions,
                'list_item_properties' => $listItemProperties,
            ]
        );
    }

}