<?php

namespace Teebb\SBAdmin2Bundle\Admin;


use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teebb\SBAdmin2Bundle\Route\RouteCollection;

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
    private $label;

    /**
     * Define a Collection of child admin, ie /admin/order/{id}/order-element/{childId}.
     *
     * @var array
     */
    private $children = [];

    /**
     * Reference the parent collection.
     *
     * @var AdminInterface|null
     */
    private $parent = null;

    /**
     * Current admin service id.
     *
     * @var string
     */
    private $adminServiceId;

    /**
     * Current admin map the parent admin entity properties;
     * @var array | null
     */
    private $mapProperties = null;

    /**
     * Current admin manage the entity object.
     *
     * @var string
     */
    private $entity;

    /**
     * The base name controller used to generate the routing information.
     *
     * @var string
     */
    private $baseControllerName;

    /**
     * The route name prefix
     * @var string
     */
    private $baseRouteName;

    private $cachedBaseRouteName;

    /**
     * @var string
     */
    private $baseRoutePattern;

    private $cachedBaseRoutePattern;

    /**
     * Array of routes related to this admin.
     *
     * @var RouteCollection
     */
    private $routes;


    /**
     * @var array
     */
    protected $loaded = [
        'routes' => false,
    ];


    public function __construct($adminServiceId, $entity, $baseControllerName)
    {
        $this->adminServiceId = $adminServiceId;
        $this->entity = $entity;
        $this->baseControllerName = $baseControllerName;

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

        $this->setParent($this);

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
        if (empty($label))
        {
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


    public function generateUrl($name, array $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {

    }

    public function isChild()
    {
        return $this->parent instanceof AdminInterface;
    }

    /**
     * urlize the given word.
     *
     * @param string $word
     * @param string $sep  the separator
     *
     * @return string
     */
    public function urlize($word, $sep = '_')
    {
        return strtolower(preg_replace('/[^a-z0-9_]/i', $sep.'$1', $word));
    }

    /**
     * Returns the baseRouteName used to generate the routing information.
     *
     * @throws \RuntimeException
     *
     * @return string the baseRouteName used to generate the routing information
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
                empty($matches[1]) ? '' : $this->urlize($matches[1]).'_',
                $this->urlize($matches[3]),
                $this->urlize($matches[5])
            );
        }

        return $this->cachedBaseRouteName;
    }

}