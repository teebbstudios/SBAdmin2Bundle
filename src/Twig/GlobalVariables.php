<?php

declare(strict_types=1);

namespace Teebb\SBAdmin2Bundle\Twig;


use Teebb\SBAdmin2Bundle\Config\TeebbSBAdmin2ConfigInterface;

class GlobalVariables
{
    /**
     * @var TeebbSBAdmin2ConfigInterface
     */
    private $SBAdmin2Config;

    public function __construct(TeebbSBAdmin2ConfigInterface $SBAdmin2Config)
    {
        $this->SBAdmin2Config = $SBAdmin2Config;
    }

    public function getSBAdmin2Config()
    {
        return $this->SBAdmin2Config;
    }
}
