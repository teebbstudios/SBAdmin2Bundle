<?php

namespace Teebb\SBAdmin2Bundle\Admin;


class AbstractAdmin implements AdminInterface
{
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
}