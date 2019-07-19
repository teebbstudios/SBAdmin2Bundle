<?php

namespace Teebb\SBAdmin2Bundle\Admin;


use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface AdminInterface extends ParentAdminInterface
{

    public function addChild(AdminInterface $child, string $property);

    public function getAdminServiceId();

    /**
     * @return AdminInterface|null
     */
    public function getParent();

    public function setParent(AdminInterface $admin);

    public function hasChild($adminServiceId);

    public function getChildren();

    public function getChild($adminServiceId);

    /**
     * Generates a url for the given parameters.
     *
     * @param string $name
     * @param int    $absolute
     *
     * @return array return url parts: 'route', 'routeParameters', 'routeAbsolute'
     */
    public function generateUrl($name, array $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH);

    /**
     * 设置Admin菜单的默认Label
     * @param string $adminLabel
     */
    public function setLabel(string $adminLabel): void;

    public function getLabel(): string;
}