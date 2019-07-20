<?php

namespace Teebb\SBAdmin2Bundle\Config;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Teebb\SBAdmin2Bundle\Admin\AdminInterface;

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
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    private $favicon;

    /**
     * @var array
     */
    private $menuGroups;

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


    public function __construct(ContainerInterface $container, $logoText, $logoImage, $favicon, $options=[])
    {
        $this->container = $container;

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

    public function getMenuGroups(): array
    {
        return $this->menuGroups;
    }

    public function setMenuGroups(array $menuGroups = [])
    {
        $this->menuGroups = $menuGroups;
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

    public function getInstance(string $adminServiceId): AdminInterface
    {
        if (!\in_array($adminServiceId, $this->adminServiceIds, true)) {
            $msg = sprintf('Admin service "%s" not found in admin pool.', $adminServiceId);
            $shortest = -1;
            $closest = null;
            $alternatives = [];
            foreach ($this->adminServiceIds as $adminServiceId) {
                $lev = levenshtein($adminServiceId, $adminServiceId);
                if ($lev <= $shortest || $shortest < 0) {
                    $closest = $adminServiceId;
                    $shortest = $lev;
                }
                if ($lev <= \strlen($adminServiceId) / 3 || false !== strpos($adminServiceId, $adminServiceId)) {
                    $alternatives[$adminServiceId] = $lev;
                }
            }
            if (null !== $closest) {
                asort($alternatives);
                unset($alternatives[$closest]);
                $msg = sprintf(
                    'Admin service "%s" not found. Did you mean "%s" or one of those: [%s]?',
                    $adminServiceId,
                    $closest,
                    implode(', ', array_keys($alternatives))
                );
            }
            throw new \InvalidArgumentException($msg);
        }

        $admin = $this->container->get($adminServiceId);

        if (!$admin instanceof AdminInterface) {
            throw new \InvalidArgumentException(sprintf('Found service "%s" is not a valid admin service', $adminServiceId));
        }

        return $admin;
    }

}