<?php

namespace Teebb\SBAdmin2Bundle\Twig;

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

    public function __construct(TeebbSBAdmin2ConfigInterface $sbadmin2Config)
    {
        $this->sbadmin2Config = $sbadmin2Config;
        $this->transDomain = $sbadmin2Config->getOption('default_label_catalogue');
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


}
