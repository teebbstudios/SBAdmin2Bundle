<?php

namespace Teebb\SBAdmin2Bundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
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

    }

    public function listAction(Request $request)
    {
        $this->admin->checkAccess('list');


    }

}