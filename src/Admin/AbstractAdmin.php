<?php

namespace Teebb\SBAdmin2Bundle\Admin;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Teebb\SBAdmin2Bundle\Exception\FilterPropertyNotFoundException;
use Teebb\SBAdmin2Bundle\Form\Type\FilterButtonType;
use Teebb\SBAdmin2Bundle\Route\RouteBuilderInterface;
use Teebb\SBAdmin2Bundle\Route\RouteCollection;
use Teebb\SBAdmin2Bundle\Route\RouteGeneratorInterface;
use Teebb\SBAdmin2Bundle\Security\SecurityHandlerInterface;
use Teebb\SBAdmin2Bundle\Translator\LabelTranslatorStrategyInterface;

class AbstractAdmin implements AdminInterface
{
    public const CLASS_REGEX =
        '@
        (?:([A-Za-z0-9]*)\\\)?        # vendor name / app name
        (Bundle\\\)?                  # optional bundle directory
        ([A-Za-z0-9]+?)(?:Bundle)?\\\ # bundle name, with optional suffix
        (
            Entity|Document|Model|PHPCR|CouchDocument|Phpcr|
            Doctrine\\\Orm|Doctrine\\\Phpcr|Doctrine\\\MongoDB|Doctrine\\\CouchDB
        )\\\(.*)@x';

    /**
     * Admin label, show in the menu.
     * @var string
     */
    protected $label;

    /**
     * Define a Collection of child admin, ie /admin/order/{id}/order-element/{childId}.
     *
     * @var array
     */
    protected $children = [];

    /**
     * Reference the parent collection.
     *
     * @var AdminInterface|null
     */
    protected $parent = null;

    /**
     * Current admin service id.
     *
     * @var string
     */
    protected $adminServiceId;

    /**
     * Current admin map the parent admin entity properties;
     * @var array | null
     */
    protected $mapProperties = null;

    /**
     * Current admin manage the entity object.
     *
     * @var string
     */
    protected $entity;

    /**
     * The base name controller used to generate the routing information.
     *
     * @var string
     */
    protected $baseControllerName;

    /**
     * @var Request
     */
    protected $request;

    /**
     * The route name prefix
     * @var string
     */
    protected $baseRouteName;

    protected $cachedBaseRouteName;

    /**
     * @var string
     */
    protected $baseRoutePattern;

    protected $cachedBaseRoutePattern;

    /**
     * Array of routes related to this admin.
     *
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @var array
     */
    protected $loaded = [
        'routes' => false,
    ];

    /**
     * @var RouteBuilderInterface
     */
    protected $routeBuilder;

    /**
     * @var string
     */
    protected $translationDomain;

    /**
     * @var RouteGeneratorInterface
     */
    protected $routeGenerator;

    /**
     * @var FactoryInterface
     */
    protected $menuFactory;

    /**
     * @var string
     */
    protected $entityClassLabel;

    /**
     * The admin create edit list delete configs
     * @var array
     */
    protected $crudConfigs;

    /**
     * The admin rest settings
     * @var array
     */
    protected $rest;

    /**
     * @var SecurityHandlerInterface
     */
    protected $securityHandler;

    protected $cacheIsGranted = [];

    /**
     * The subject only set in edit/update/create mode.
     *
     * @var object|null
     */
    protected $subject;

    /**
     * The children Admin is current
     * @var bool
     */
    protected $boolCurrentChild;

    /**
     * @var LabelTranslatorStrategyInterface
     */
    protected $labelTranslatorStrategy;

    /**
     * @var EntityManagerInterface
     */
    protected $objectManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var FormRegistryInterface
     */
    protected $formRegistry;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    public function __construct($adminServiceId, $entity, $baseControllerName)
    {
        $this->adminServiceId = $adminServiceId;

        $this->entity = $entity;

        $this->baseControllerName = $baseControllerName;
    }

    public function getEntityClass()
    {
        return $this->entity;
    }

    public function addChild(AdminInterface $child, string $property)
    {
        for ($parentAdmin = $this; null !== $parentAdmin; $parentAdmin = $parentAdmin->getParent()) {
            if ($parentAdmin->getAdminServiceId() !== $child->getAdminServiceId()) {
                continue;
            }

            throw new \RuntimeException(sprintf(
                'Circular reference detected! The child admin `%s` is already in the parent tree of the `%s` admin.',
                $child->getAdminServiceId(), $this->getAdminServiceId()
            ));
        }

        $this->children[$child->getAdminServiceId()] = $child;

        $this->mapProperties[$this->adminServiceId] = $property;

    }

    public function getAdminServiceId()
    {
        return $this->adminServiceId;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(AdminInterface $admin)
    {
        $this->parent = $admin;
    }

    public function hasChild($adminServiceId)
    {
        return isset($this->children[$adminServiceId]);
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getChild($adminServiceId)
    {
        return $this->children[$adminServiceId];
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        if (empty($label)) {
            $this->label = get_class($this);
        }

        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    public function setRequest(Request $request)
    {
        return $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function hasRequest()
    {
        return null !== $this->request;
    }

    public function isChild()
    {
        return $this->parent instanceof AdminInterface;
    }

    /**
     * urlize the given word.
     *
     * @param string $word
     * @param string $sep the separator
     *
     * @return string
     */
    public function urlize($word, $sep = '_')
    {
        return strtolower(preg_replace('/[^a-z0-9_]/i', $sep . '$1', $word));
    }

    public function getBaseCodeRoute()
    {
        if ($this->isChild()) {
            return $this->getParent()->getBaseCodeRoute() . '|' . $this->adminServiceId;
        }

        return $this->adminServiceId;
    }

    /**
     * Returns the baseRouteName used to generate the routing information.
     *
     * @return string the baseRouteName used to generate the routing information
     * @throws \RuntimeException
     *
     */
    public function getBaseRouteName()
    {
        if (null !== $this->cachedBaseRouteName) {
            return $this->cachedBaseRouteName;
        }

        if ($this->isChild()) { // the admin class is a child, prefix it with the parent route name
            $baseRouteName = $this->baseRouteName;
            if (!$this->baseRouteName) {
                preg_match(self::CLASS_REGEX, $this->entity, $matches);

                if (!$matches) {
                    throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', \get_class($this)));
                }
                $baseRouteName = $this->urlize($matches[5]);
            }

            $this->cachedBaseRouteName = sprintf(
                '%s_%s',
                $this->getParent()->getBaseRouteName(),
                $baseRouteName
            );
        } elseif ($this->baseRouteName) {
            $this->cachedBaseRouteName = $this->baseRouteName;
        } else {
            preg_match(self::CLASS_REGEX, $this->entity, $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', \get_class($this)));
            }

            $this->cachedBaseRouteName = sprintf('admin_%s%s_%s',
                empty($matches[1]) ? '' : $this->urlize($matches[1]) . '_',
                $this->urlize($matches[3]),
                $this->urlize($matches[5])
            );
        }

        return $this->cachedBaseRouteName;
    }

    /**
     * Returns the baseRoutePattern used to generate the routing information.
     *
     * @return string the baseRoutePattern used to generate the routing information
     * @throws \RuntimeException
     *
     */
    public function getBaseRoutePattern()
    {
        if (null !== $this->cachedBaseRoutePattern) {
            return $this->cachedBaseRoutePattern;
        }

        if ($this->isChild()) { // the admin class is a child, prefix it with the parent route pattern
            $baseRoutePattern = $this->baseRoutePattern;
            if (!$this->baseRoutePattern) {
                preg_match(self::CLASS_REGEX, $this->entity, $matches);

                if (!$matches) {
                    throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', \get_class($this)));
                }
                $baseRoutePattern = $this->urlize($matches[5], '-');
            }

            $this->cachedBaseRoutePattern = sprintf(
                '%s/%s/%s',
                $this->getParent()->getBaseRoutePattern(),
                $this->getParent()->getRouterIdParameter(),
                $baseRoutePattern
            );
        } elseif ($this->baseRoutePattern) {
            $this->cachedBaseRoutePattern = $this->baseRoutePattern;
        } else {
            preg_match(self::CLASS_REGEX, $this->entity, $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', \get_class($this)));
            }

            $this->cachedBaseRoutePattern = sprintf(
                '/%s%s/%s',
                empty($matches[1]) ? '' : $this->urlize($matches[1], '-') . '/',
                $this->urlize($matches[3], '-'),
                $this->urlize($matches[5], '-')
            );
        }

        return $this->cachedBaseRoutePattern;
    }

    public function getRouterIdParameter()
    {
        return '{' . $this->getIdParameter() . '}';
    }

    public function getIdParameter()
    {
        $parameter = 'id';

        for ($i = 0; $i < $this->getChildDepth(); ++$i) {
            $parameter = 'child' . ucfirst($parameter);
        }

        return $parameter;
    }

    final public function getChildDepth()
    {
        $parent = $this;
        $depth = 0;

        while ($parent->isChild()) {
            $parent = $parent->getParent();
            ++$depth;
        }

        return $depth;
    }

    public function getBaseControllerName()
    {
        return $this->baseControllerName;
    }

    public function getRoutes()
    {
        $this->buildRoutes();

        return $this->routes;
    }

    public function buildRoutes()
    {
        if ($this->loaded['routes']) {
            return;
        }

        $this->loaded['routes'] = true;

        $this->routes = new RouteCollection(
            $this->getBaseCodeRoute(),
            $this->getBaseRouteName(),
            $this->getBaseRoutePattern(),
            $this->getBaseControllerName()
        );

        $this->routeBuilder->build($this, $this->routes);
    }

    public function setRouteBuilder(RouteBuilderInterface $routeBuilder)
    {
        $this->routeBuilder = $routeBuilder;
    }

    public function getRouteBuilder()
    {
        return $this->routeBuilder;
    }


    public function setTranslationDomain(string $translationDomain)
    {
        $this->translationDomain = $translationDomain;
    }

    public function getTranslationDomain()
    {
        return $this->translationDomain;
    }

    public function generateUrl($name, array $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->routeGenerator->generateUrl($this, $name, $parameters, $absolute);
    }

    public function generateMenuUrl($name, array $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->routeGenerator->generateMenuUrl($this, $name, $parameters, $absolute);
    }

    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator)
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * @return RouteGeneratorInterface
     */
    public function getRouteGenerator()
    {
        return $this->routeGenerator;
    }

    /**
     * @return mixed
     */
    public function getMenuFactory(): FactoryInterface
    {
        return $this->menuFactory;
    }

    /**
     * @param mixed $menuFactory
     */
    public function setMenuFactory($menuFactory): void
    {
        $this->menuFactory = $menuFactory;
    }

    /**
     * @return string
     */
    public function getEntityClassLabel(): string
    {
        return $this->entityClassLabel;
    }

    /**
     * @param string $entityClassLabel
     */
    public function setEntityClassLabel(string $entityClassLabel): void
    {
        $this->entityClassLabel = $entityClassLabel;
    }

    /**
     * define custom variable.
     */
    public function initialize()
    {
        if (!$this->entityClassLabel) {
            $this->entityClassLabel = substr(
                (string)$this->getEntityClass(),
                strrpos($this->getEntityClass(), '\\') + 1
            );
        }
    }

    public function hasRoute($name)
    {
        if (!$this->routeGenerator) {
            throw new \RuntimeException('RouteGenerator cannot be null');
        }

        return $this->routeGenerator->hasAdminRoute($this, $name);
    }

    /**
     * @return array
     */
    public function getCrudConfigs(): array
    {
        return $this->crudConfigs;
    }

    /**
     * @param array $crudConfigs
     */
    public function setCrudConfigs(array $crudConfigs): void
    {
        $this->crudConfigs = $crudConfigs;
    }

    /**
     * @return array
     */
    public function getRest(): array
    {
        return $this->rest;
    }

    /**
     * @param array $rest
     */
    public function setRest(array $rest): void
    {
        $this->rest = $rest;
    }

    /**
     * @return SecurityHandlerInterface
     */
    public function getSecurityHandler(): SecurityHandlerInterface
    {
        return $this->securityHandler;
    }

    /**
     * @param SecurityHandlerInterface $securityHandler
     */
    public function setSecurityHandler(SecurityHandlerInterface $securityHandler): void
    {
        $this->securityHandler = $securityHandler;
    }

    public function isGranted($name, $object = null)
    {
        $key = md5(json_encode($name) . ($object ? '/' . spl_object_hash($object) : ''));

        if (!\array_key_exists($key, $this->cacheIsGranted)) {
            $this->cacheIsGranted[$key] = $this->securityHandler->isGranted($this, $name, $object ?: $this);
        }

        return $this->cacheIsGranted[$key];
    }

    public function hasAccess($action, $object = null): bool
    {
        if (!\array_key_exists($action, $this->crudConfigs)) {
            return false;
        }

        foreach ($this->crudConfigs[$action]['permission']['roles'] as $role) {
            dd($role);

            if (false === $this->isGranted($role, $object)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return object|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param object|null $subject
     */
    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    public function hasSubject(): bool
    {
        return (bool)$this->getSubject();
    }

    public function toString($object)
    {
        if (!\is_object($object)) {
            return '';
        }

        if (method_exists($object, '__toString') && null !== $object->__toString()) {
            return (string)$object;
        }

        return sprintf('%s:%s', get_class($object), spl_object_hash($object));
    }

    /**
     * Returns the current child admin instance.
     *
     * @return AdminInterface|null the current child admin instance
     */
    public function getCurrentChildAdmin()
    {
        foreach ($this->children as $children) {
            if ($children->getBoolCurrentChild()) {
                return $children;
            }
        }
    }

    public function getBoolCurrentChild()
    {
        return $this->boolCurrentChild;
    }

    public function setBoolCurrentChild(bool $boolCurrentChild)
    {
        $this->boolCurrentChild = $boolCurrentChild;
    }

    /**
     * @return mixed
     */
    public function getLabelTranslatorStrategy(): LabelTranslatorStrategyInterface
    {
        return $this->labelTranslatorStrategy;
    }

    /**
     * @param mixed $labelTranslatorStrategy
     */
    public function setLabelTranslatorStrategy($labelTranslatorStrategy): void
    {
        $this->labelTranslatorStrategy = $labelTranslatorStrategy;
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager(): ObjectManager
    {
        return $this->objectManager;
    }

    /**
     * @param ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return FormFactoryInterface
     */
    public function getFormFactory(): FormFactoryInterface
    {
        return $this->formFactory;
    }

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @return FormRegistryInterface
     */
    public function getFormRegistry(): FormRegistryInterface
    {
        return $this->formRegistry;
    }

    /**
     * @param FormRegistryInterface $formRegistry
     */
    public function setFormRegistry(FormRegistryInterface $formRegistry): void
    {
        $this->formRegistry = $formRegistry;
    }

    /**
     * @return PropertyAccessorInterface
     */
    public function getPropertyAccessor(): PropertyAccessorInterface
    {
        return $this->propertyAccessor;
    }

    /**
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): void
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Check the crud permission.
     * @param $action : The crud action
     * @param null $object
     */
    public function checkAccess($action, $object = null)
    {
        if (!\array_key_exists($action, $this->crudConfigs)) {
            throw new \InvalidArgumentException(sprintf(
                'Action "%s" could not be found in access mapping.'
                . ' Please make sure your action is defined into your admin class accessMapping property.',
                $action
            ));
        }

        foreach ($this->crudConfigs[$action]['permission']['roles'] as $role) {
            if (false === $this->isGranted($role, $object)) {
                throw new AccessDeniedException(sprintf('Access Denied to the action %s and role %s', $action, $role));
            }
        }
    }

    public function getResults()
    {
        return $this->objectManager->getRepository($this->entity)->findAll();
    }

    public function getConditionalQueryResults(array $condition, array $orders = null)
    {
        $qb = $this->objectManager->createQueryBuilder();

        $queryBuilder = $qb->select('o')
            ->from($this->entity, 'o');

        $metaData = $this->objectManager->getMetadataFactory()->getMetadataFor($this->entity);

        foreach ($condition as $filterName => $filterValue) {
            switch ($metaData->getTypeOfField($filterName)) {
                case 'string':
                    if (!empty($filterValue)) {
                        $where = $qb->expr()->like('o.' . $filterName, ':' . $filterName);
                        $queryBuilder->andWhere($where)->setParameter($filterName, '%' . $filterValue . '%');
                    }
                    break;
                case 'integer':
                    if (!empty($filterValue)) {
                        $where = $qb->expr()->eq('o.' . $filterName, ':' . $filterName);
                        $queryBuilder->andWhere($where)->setParameter($filterName, $filterValue);
                    }
                    break;
            }
        }

        return $queryBuilder->getQuery();
    }

    public function getAccessFilterProperties(): array
    {
        $filters = $this->crudConfigs['list']['filters'];
        $filterProperties = [];
        foreach ($filters as $filter) {
            if (property_exists(new $this->entity, $filter['property'])) {
                array_push($filterProperties, $filter);
            }
        }
        return $filterProperties;
    }

    public function getListProperties(): array
    {
        $fields = $this->crudConfigs['list']['fields'];
        $fieldsArray = [];
        foreach ($fields as $field) {
            if (property_exists(new $this->entity, $field['property'])) {
                array_push($fieldsArray, $field);
            }
        }
        return $fieldsArray;
    }

    public function getListItemActions(): array
    {
        $actions = $this->crudConfigs['list']['actions'];

        $actionsArray = [];
        foreach ($actions as $action) {

            if ($this->hasRoute($action['name'])) {
                array_push($actionsArray, $action);
            } else {
                throw new \Exception(
                    sprintf('The list item action %s not found. Check the %s configure list actions name.', $action['name'], $this->getAdminServiceId())
                );
            }
        }
        return $actionsArray;
    }

    public function getListFilterForm(): FormInterface
    {
        if (empty($filters = $this->crudConfigs['list']['filters'])) {
            return null;
        }

        $formBuilder = $this->formFactory->createNamedBuilder('filter', FormType::class, null, ['csrf_protection' => false, 'allow_extra_fields' => true]);
        $formBuilder->setMethod('get');

        $typeGuesser = $this->formRegistry->getTypeGuesser();

        foreach ($filters as $filter) {
            if (empty($filter['roles']) || $this->isGranted($filter['roles'])) {
                if (!property_exists(new $this->entity, $filter['property'])) {
                    throw new FilterPropertyNotFoundException(sprintf('The filter property %s not found in class %s. Please check the admin config files.', $filter['property'], $this->entity));
                }
                $type = $typeGuesser->guessType($this->entity, $filter['property'])->getType();

                $options = array(
                    'required' => false,
                    'label_attr' => [
                        'class' => 'sr-only',
                    ],
                    'translation_domain' => $this->translationDomain,
                    'attr' => [
                        'class' => 'form-control-sm',
                        'placeholder' => $filter['property'],
                    ],
                );

                $formBuilder->add($filter['property'], $filter['type'] ?? $type, array_merge_recursive($options, $filter['options']));
            }
        }
        $formBuilder->add('Filter', FilterButtonType::class, ['translation_domain' => $this->translationDomain, 'attr' => ['row_class' => 'col-12 col-md-2 mb-2 mb-md-0']]);

        return $formBuilder->getForm();
    }


}