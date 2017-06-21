<?php

namespace Search\Model;

class User
{
    protected $roles;

    /**
     * User constructor.
     * @param $roles
     */
    public function __construct()
    {
        $this->roles = [
            'ROLE_ANONYME_10'
        ];
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }
}
