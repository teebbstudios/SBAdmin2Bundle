<?php

namespace Teebb\SBAdmin2Bundle\Admin;


interface AdminInterface extends ParentAdminInterface
{

    public function getAdminServiceId();

    /**
     * @return AdminInterface|null
     */
    public function getParent();
    public function setParent(AdminInterface $admin);
}