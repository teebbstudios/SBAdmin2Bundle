<?php

namespace Teebb\SBAdmin2Bundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Teebb\SBAdmin2Bundle\Config\TeebbSBAdmin2ConfigInterface;
use Teebb\SBAdmin2Bundle\Templating\TemplateRegistryInterface;

class DashboardController extends AbstractController
{
    /**
     * @var array
     */
    private $dashboardBlocks=[];

    /**
     * @var TeebbSBAdmin2ConfigInterface
     */
    private $sbadmin2Config;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    public function __construct(array $dashboardBlocks, TeebbSBAdmin2ConfigInterface $sbadmin2Config, TemplateRegistryInterface $templateRegistry)
    {
        $this->dashboardBlocks = $dashboardBlocks;

        $this->sbadmin2Config = $sbadmin2Config;

        $this->templateRegistry = $templateRegistry;
    }

    public function __invoke(Request $request)
    {
        $blocks = [
            'top' => [],
            'left' => [],
            'center' => [],
            'right' => [],
            'bottom' => [],
        ];

        foreach ($this->dashboardBlocks as $block) {
            $blocks[$block['position']][] = $block;
        }

        $parameters = [
            'base_template' => $this->templateRegistry->getTemplate('layout'),
            'sbadmin2_config' => $this->sbadmin2Config,
            'blocks' => $blocks,
        ];

        return $this->render($this->templateRegistry->getTemplate('dashboard'), $parameters);
    }

}