<?php


namespace Teebb\SBAdmin2Bundle\Config;


interface TeebbSBAdmin2ConfigInterface
{
    public function getLogoText(): string;

    public function getLogoImage(): string;

    public function getFavicon(): string;

    public function setMenuGroups();

    public function getMenuGroups(): array;

    public function getOption(string $optionName);

    /**
     * @param string $templateName
     * @return string
     */
    public function getTemplate(string $templateName): string;

    public function getInstance(string $adminServiceId);
}