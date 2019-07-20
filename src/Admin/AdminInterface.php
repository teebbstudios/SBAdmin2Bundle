<?php

namespace Teebb\SBAdmin2Bundle\Admin;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teebb\SBAdmin2Bundle\Route\RouteBuilderInterface;
use Teebb\SBAdmin2Bundle\Route\RouteCollection;
use Teebb\SBAdmin2Bundle\Route\RouteGeneratorInterface;

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

    /**
     * @return AdminInterface|null
     */
    public function getChildren();

    public function getChild($adminServiceId);

    public function isChild();

    /**
     * 设置Admin菜单的默认Label
     * @param string $adminLabel
     */
    public function setLabel(string $adminLabel): void;

    public function getLabel(): string;

    public function getBaseCodeRoute();

    public function getBaseRouteName();

    public function getBaseRoutePattern();

    /**
     * Return the parameter name used to represent the id in the url.
     *
     * @return string
     */
    public function getRouterIdParameter();

    public function getBaseControllerName();

    /**
     * @return RouteCollection
     */
    public function getRoutes();

    public function buildRoutes();

    public function setRouteBuilder(RouteBuilderInterface $routeBuilder);

    public function getRouteBuilder();

    public function getIdParameter();

    /**
     * When controller action called the Request will pass to Admin
     * @param Request $request
     * @return mixed
     */
    public function setRequest(Request $request);

    public function getRequest();

    public function hasRequest();

    public function setTranslationDomain(string $translationDomain);

    public function getTranslationDomain();

    /**
     * Generates a url for the given parameters.
     *
     * @param string $name
     * @param int    $absolute
     *
     * @return array return url parts: 'route', 'routeParameters', 'routeAbsolute'
     */
    public function generateUrl($name, array $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH);

    public function generateMenuUrl($name, array $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH);

    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator);

    /**
     * @return RouteGeneratorInterface
     */
    public function getRouteGenerator();
}