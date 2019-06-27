<?php

namespace Teebb\SBAdmin2Bundle\Config;


class TeebbSBAdmin2Config implements TeebbSBAdmin2ConfigInterface
{
    /**
     * @var string
     */
    private $logoText;

    /**
     * @var string
     */
    private $logoImage;

    /**
     * @var string
     */
    private $favicon;

    /**
     * @var array
     */
    private $adminGroups;

    /**
     * @var array
     */
    private $adminServiceIds;

    /**
     * @var array
     */
    private $entityClasses;

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $templates=[];


    public function __construct($logoText, $logoImage, $favicon, $options=[])
    {
        $this->logoText = $logoText;

        $this->logoImage = $logoImage;

        $this->favicon = $favicon;

        $this->options = $options;
    }


    /**
     * @return string
     */
    public function getLogoText(): string
    {
        return $this->logoText;
    }

    /**
     * @return string
     */
    public function getLogoImage(): string
    {
        return $this->logoImage;
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

    public function getAdminGroups(): array
    {
        return $this->adminGroups;
    }

    public function setAdminGroups(array $adminGroups = [])
    {
        $this->adminGroups = $adminGroups;
    }

    /**
     * @return array
     */
    public function getAdminServiceIds(): array
    {
        return $this->adminServiceIds;
    }

    /**
     * @param array $adminServiceIds
     */
    public function setAdminServiceIds(array $adminServiceIds): void
    {
        $this->adminServiceIds = $adminServiceIds;
    }

    /**
     * @return array
     */
    public function getEntityClasses(): array
    {
        return $this->entityClasses;
    }

    /**
     * @param array $entityClasses
     */
    public function setEntityClasses(array $entityClasses): void
    {
        $this->entityClasses = $entityClasses;
    }

    /**
     * @param array $templates
     */
    public function setTemplates(array $templates): void
    {
        $this->templates = $templates;
    }

    public function getTemplate(string $templateName): string
    {
       return $this->templates[$templateName];
    }


}