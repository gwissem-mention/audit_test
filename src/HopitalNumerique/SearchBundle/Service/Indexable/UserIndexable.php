<?php

namespace HopitalNumerique\SearchBundle\Service\Indexable;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use \HopitalNumerique\UserBundle\Entity\User as UserEntity;
use Nodevo\RoleBundle\Entity\Role;

/**
 * User type indexable.
 * This class is responsible of saying if a User is indexable
 */
class UserIndexable
{
    /**
     * @var string
     */
    protected $domaineSlug;

    /**
     * UserIndexable constructor.
     *
     * @param string $domaineSlug
     */
    public function __construct($domaineSlug)
    {
        $this->domaineSlug = $domaineSlug;
    }

    /**
     * Check if $user is indexable
     *
     * @param UserEntity $user
     * @return bool
     */
    public function isIndexable(UserEntity $user)
    {
        $roleAllowed = false;
        foreach (self::getRoles() as $role) {
            $roleAllowed = $roleAllowed || $user->hasRole($role);
        }

        return $roleAllowed && !$user->getDomaines()
            ->map(function (Domaine $domaine) {
                return $domaine->getSlug();
            })
            ->filter(function ($slug) {
                return $slug === $this->domaineSlug;
            })
            ->isEmpty()
        ;
    }

    /**
     * Get user indexables roles
     *
     * @return array
     */
    public static function getRoles()
    {
        return [
            Role::$ROLE_AMBASSADEUR_LABEL,
            Role::$ROLE_EXPERT_LABEL,
        ];
    }
}
