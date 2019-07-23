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

    /**
     * Returns an admin class by its Admin code
     * ie : sonata.news.admin.post|sonata.news.admin.comment => return the child class of post.
     *
     * @param string $adminCode
     *
     * @throws \InvalidArgumentException if the root admin code is an empty string
     *
     * @return AdminInterface|false
     */
    public function getAdminByAdminCode($adminCode)
    {
        if (!\is_string($adminCode)) {
            @trigger_error(sprintf(
                'Passing a non string value as argument 1 for %s() is deprecated since sonata-project/admin-bundle 3.51 and will throw an exception in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);

            return false;

            // NEXT_MAJOR : remove this condition check and declare "string" as type without default value for argument 1
        }
        $codes = explode('|', $adminCode);
        $code = trim(array_shift($codes));

        if ('' === $code) {
            throw new \InvalidArgumentException('Root admin code must contain a valid admin reference, empty string given.');
        }

        $admin = $this->getInstance($code);

        foreach ($codes as $code) {
            if (!\in_array($code, $this->adminServiceIds, true)) {
                @trigger_error(sprintf(
                    'Passing an invalid admin code as argument 1 for %s() is deprecated since sonata-project/admin-bundle 3.50 and will throw an exception in 4.0.',
                    __METHOD__
                ), E_USER_DEPRECATED);

                // NEXT_MAJOR : throw `\InvalidArgumentException` instead
            }

            if (!$admin->hasChild($code)) {
                @trigger_error(sprintf(
                    'Passing an invalid admin hierarchy inside argument 1 for %s() is deprecated since sonata-project/admin-bundle 3.51 and will throw an exception in 4.0.',
                    __METHOD__
                ), E_USER_DEPRECATED);

                // NEXT_MAJOR : remove the previous `trigger_error()` call, uncomment the following excception and declare AdminInterface as return type
                // throw new InvalidArgumentException(sprintf(
                //    'Argument 1 passed to %s() must contain a valid admin hierarchy, "%s" is not a valid child for "%s"',
                //    __METHOD__,
                //    $code,
                //    $admin->getCode()
                // ));

                return false;
            }

            $admin = $admin->getChild($code);
        }

        return $admin;
    }

}