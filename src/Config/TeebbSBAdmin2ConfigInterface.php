<?php


namespace Teebb\SBAdmin2Bundle\Config;


use Teebb\SBAdmin2Bundle\Admin\AdminInterface;

interface TeebbSBAdmin2ConfigInterface
{
    public function getLogoText(): string;

    public function getLogoImage(): string;

    public function getFavicon(): string;

    public function getOption(string $optionName);

    public function setMenuGroups();

    public function getMenuGroups(): array;

    public function setAdminServiceIds(array $adminServiceIds): void;

    public function getAdminServiceIds(): array;

    public function setEntityClasses(array $entityClasses): void;

    public function getEntityClasses(): array;

    public function setTemplates(array $templates): void;

    /**
     * @param string $templateName
     * @return string
     */
    public function getTemplate(string $templateName): string;

    public function getInstance(string $adminServiceId): AdminInterface;
}