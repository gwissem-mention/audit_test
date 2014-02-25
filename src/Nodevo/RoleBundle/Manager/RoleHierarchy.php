<?php

namespace Nodevo\RoleBundle\Manager;

use Doctrine\ORM\EntityManager;
use \Symfony\Component\Security\Core\Role\RoleHierarchy as Hierarchy;

class RoleHierarchy extends Hierarchy
{
    private $em;

    /**
     * @param array $hierarchy
     */
    public function __construct(array $hierarchy, EntityManager $em)
    {
        $this->em = $em;
        parent::__construct($this->buildRolesTree());
    }

    /**
     * Here we build an array with roles. It looks like a two-levelled tree - just 
     * like original Symfony roles are stored in security.yml
     * @return array
     */
    private function buildRolesTree()
    {
        $hierarchy = array();
        $roles     = $this->em->createQuery('select r from NodevoRoleBundle:Role r')->execute();

        foreach ($roles as $role) {
            /** @var $role Role */
            if ( !isset( $hierarchy[ $role->getRole() ] ) )
                $hierarchy[$role->getRole()] = array();
        }

        return $hierarchy;
    }
}