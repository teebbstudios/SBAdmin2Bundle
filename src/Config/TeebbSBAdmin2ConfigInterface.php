<?php


namespace Teebb\SBAdmin2Bundle\Config;


interface TeebbSBAdmin2ConfigInterface
{
    public function getLogoText(): string;

    public function getLogoImage(): string;

    public function getFavicon(): string;

    public function setAdminGroups();

    public function getAdminGroups(): array;

    public function getOption(string $optionName);

}