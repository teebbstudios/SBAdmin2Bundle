<?php


namespace Teebb\SBAdmin2Bundle\Config;


interface TeebbSBAdmin2ConfigInterface
{
    public function getSiteName(): string;

    public function getSiteLogo(): string;

    public function getFavicon(): string;

    public function getOption(string $optionName);

}