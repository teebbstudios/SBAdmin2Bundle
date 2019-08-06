<?php

namespace Teebb\SBAdmin2Bundle\Admin;

use Doctrine\Common\Persistence\ObjectManager;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teebb\SBAdmin2Bundle\Exception\PropertyNotExistException;
use Teebb\SBAdmin2Bundle\Route\RouteBuilderInterface;
use Teebb\SBAdmin2Bundle\Route\RouteCollection;
use Teebb\SBAdmin2Bundle\Route\RouteGeneratorInterface;
use Teebb\SBAdmin2Bundle\Security\SecurityHandlerInterface;
use Teebb\SBAdmin2Bundle\Translator\LabelTranslatorStrategyInterface;

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

    public function getRequest(): Request;

    public function hasRequest();

    public function setTranslationDomain(string $translationDomain);

    public function getTranslationDomain();

    /**
     * Generates a url for the given parameters.
     *
     * @param string $name
     * @param int $absolute
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

    public function getMenuFactory(): FactoryInterface;

    public function setMenuFactory($menuFactory): void;

    public function getEntityClassLabel(): string;

    public function setEntityClassLabel(string $entityClassLabel): void;

    public function getEntityClass();

    public function hasRoute($name);

    public function getCrudConfigs(): array;

    public function setCrudConfigs(array $crudConfigs): void;

    public function getBatchActions(): array;

    public function setBatchActions(array $batchActions): void;

    public function getRest(): array;

    public function setRest(array $rest): void;

    public function hasAccess($action, $object = null): bool;

    public function isGranted($name, $object = null);

    public function getSecurityHandler(): SecurityHandlerInterface;

    public function setSecurityHandler(SecurityHandlerInterface $securityHandler): void;

    public function getSubject();

    public function setSubject($subject): void;

    public function hasSubject(): bool;

    public function toString($object);

    public function getCurrentChildAdmin();

    public function getBoolCurrentChild();

    public function setBoolCurrentChild(bool $boolCurrentChild);

    public function getLabelTranslatorStrategy(): LabelTranslatorStrategyInterface;

    public function setLabelTranslatorStrategy($labelTranslatorStrategy): void;

    public function getObjectManager(): ObjectManager;

    public function setObjectManager(ObjectManager $objectManager): void;

    public function getFormFactory(): FormFactoryInterface;

    public function setFormFactory(FormFactoryInterface $formFactory): void;

    public function getFormRegistry(): FormRegistryInterface;

    public function setFormRegistry(FormRegistryInterface $formRegistry): void;

    public function getPropertyAccessor(): PropertyAccessorInterface;

    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): void;

    public function checkAccess($action, $object = null);

    /**
     * Get the filter properties which in the entity class;
     * @return array
     */
    public function getAccessFilterProperties(): array;

    /**
     * Get the entity properties for list table
     * @return array
     */
    public function getListProperties(): array;

    /**
     * Get the list table item actions
     * @return array
     */
    public function getListItemActions(): array;

    /*Get all results*/
    public function getResults();

    public function getConditionalQueryResults(array $condition, array $orders = null);

    /**
     * @return FormInterface|null
     * @throws PropertyNotExistException
     */
    public function getListFilterForm();

    /**
     * Get current entity object
     * @param $id
     * @return mixed
     */
    public function getEntityObject($id);

    public function getFormConfigs(): array;

    public function setFormConfigs(array $formConfigs): void;

    public function getListActionType(): string;

    public function setListActionType(string $listActionType): void;


    public function getForm(string $action): FormInterface;
}