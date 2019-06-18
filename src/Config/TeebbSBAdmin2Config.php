<?php

namespace Teebb\SBAdmin2Bundle\Config;


class TeebbSBAdmin2Config implements TeebbSBAdmin2ConfigInterface
{
    /**
     * @var string
     */
    private $siteName;

    /**
     * @var string
     */
    private $siteLogo;

    /**
     * @var string
     */
    private $favicon;

    /**
     * @var array
     */
    private $options;


    public function __construct($siteName, $siteLogo, $favicon, $options=[])
    {
        $this->siteName = $siteName;

        $this->siteLogo = $siteLogo;

        $this->favicon = $favicon;

        $this->options = $options;
    }


    public function getSiteName(): string
    {
        return $this->siteName;
    }

    public function getSiteLogo(): string
    {
        return $this->siteLogo;
    }

    public function getFavicon(): string
    {
        return $this->favicon;
    }

    public function getOption(string $optionName, $default=[])
    {
        if (isset($this->options[$optionName])) {
            return $this->options[$optionName];
        }

        return $default;
    }
}