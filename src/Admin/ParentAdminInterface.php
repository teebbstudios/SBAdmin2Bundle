<?php

namespace Teebb\SBAdmin2Bundle\Admin;


interface ParentAdminInterface
{
    /**
     * add an Admin child to the current one.
     * @param AdminInterface $child
     * @param string $property The child entity in parent entity property
     */
    public function addChild(AdminInterface $child, string $property);

    /**
     * Returns true or false if an Admin child exists for the given $code.
     *
     * @param string $adminServiceId Admin service code
     *
     * @return bool True if child exist, false otherwise
     */
    public function hasChild($adminServiceId);

    /**
     * Returns an collection of admin children.
     *
     * @return array list of Admin children
     */
    public function getChildren();

    /**
     * Returns an admin child with the given $code.
     *
     * @param string $adminServiceId
     *
     * @return AdminInterface|null
     */
    public function getChild($adminServiceId);
}