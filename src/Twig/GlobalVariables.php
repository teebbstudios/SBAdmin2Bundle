<?php

namespace Teebb\SBAdmin2Bundle\Twig;

use Teebb\SBAdmin2Bundle\Admin\BreadcrumbsBuilder;
use Teebb\SBAdmin2Bundle\Admin\BreadcrumbsBuilderInterface;
use Teebb\SBAdmin2Bundle\Config\TeebbSBAdmin2ConfigInterface;

class GlobalVariables
{
    /**
     * @var TeebbSBAdmin2ConfigInterface
     */
    private $sbadmin2Config;

    /**
     * @var string
     */
    private $transDomain;

    /**
     * @var BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;


    public function __construct(TeebbSBAdmin2ConfigInterface $sbadmin2Config, BreadcrumbsBuilderInterface $breadcrumbsBuilder)
    {
        $this->sbadmin2Config = $sbadmin2Config;
        $this->transDomain = $sbadmin2Config->getOption('default_label_catalogue');
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
    }

    /**
     * @return TeebbSBAdmin2ConfigInterface
     */
    public function getSbadmin2Config(): TeebbSBAdmin2ConfigInterface
    {
        return $this->sbadmin2Config;
    }

    /**
     * @return string
     */
    public function getTransDomain(): string
    {
        return $this->transDomain;
    }

    /**
     * @return BreadcrumbsBuilder
     */
    public function getBreadcrumbsBuilder(): BreadcrumbsBuilder
    {
        return $this->breadcrumbsBuilder;
    }

}
