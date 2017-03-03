<?php

/*
 * This file is part of the CCDNForum ForumBundle
 *
 * (c) CCDN (c) CodeConsortium <http://www.codeconsortium.com/>
 *
 * Available on github <http://www.github.com/codeconsortium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HopitalNumerique\ForumBundle\Component\Helper;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @category CCDNForum
 *
 * @author   Gaëtan MELCHILSEN
 * @license  Nodevo
 */
class RoleHelper
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var array
     */
    protected $availableRoles;

    /**
     * @var array
     */
    protected $availableRoleKeys;

    /**
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext, \Nodevo\RoleBundle\Manager\RoleManager $managerRole)
    {
        $this->securityContext = $securityContext;

        // default role is array is empty.
        if (empty($availableRoles)) {
            foreach ($managerRole->getRolesAsArray() as $code => $name) {
                $availableRoles[$code] = [$name];
            }
        }

        $this->availableRoles = $availableRoles;

        // Remove the associate arrays.
        $this->availableRoleKeys = array_keys($availableRoles);
    }

    /**
     * @return array
     */
    public function getRoleHierarchy()
    {
        $roles = [];

        foreach ($this->availableRoles as $roleName => $roleSubs) {
            $subs = '<ul><li>' . implode('</li><li>', $roleSubs) . '</li></ul>';
            $roles[$roleName] = '<strong>' . $roleName . '</strong>' . ($subs != '<ul><li>' . $roleName . '</li></ul>' ? "\n" . $subs : '');
        }

        return $roles;
    }

    public function getRoleForFormulaire()
    {
        $roles = [];

        foreach ($this->availableRoles as $roleName => $roleSubs) {
            $roles[$roleName] = implode('</li><li>', $roleSubs);
        }

        return $roles;
    }

    /**
     * @return array $availableRoles
     */
    public function getAvailableRoles()
    {
        return $this->availableRoles;
    }

    /**
     * @return array $availableRoles
     */
    public function getAvailableRoleKeys()
    {
        return $this->availableRoleKeys;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param string                                              $role
     *
     * @return bool
     */
    public function hasRole(UserInterface $user, $role)
    {
        foreach ($this->availableRoles as $aRoleKey => $aRole) {
            if ($user->hasRole($aRoleKey)) {
                if (in_array($role, $aRole) || $role == $aRoleKey) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $userRoles
     *
     * @return int $highestUsersRoleKey
     */
    public function getUsersHighestRole($usersRoles)
    {
        $usersHighestRoleKey = 0;

        // Compare (A)vailable roles against (U)sers roles.
        foreach ($this->availableRoleKeys as $aRoleKey => $aRole) {
            foreach ($usersRoles as $uRole) {
                if ($uRole == $aRole && $aRoleKey > $usersHighestRoleKey) {
                    $usersHighestRoleKey = $aRoleKey;

                    break; // break because once uRole == aRole we know we cannot match anything else.
                }
            }
        }

        return $usersHighestRoleKey;
    }

    /**
     * @param array $userRoles
     *
     * @return string $role
     */
    public function getUsersHighestRoleAsName($usersRoles)
    {
        $usersHighestRoleKey = $this->getUsersHighestRole($usersRoles);

        $roles = $this->availableRoleKeys;

        if (array_key_exists($usersHighestRoleKey, $roles)) {
            return $roles[$usersHighestRoleKey];
        } else {
            return 'ROLE_USER';
        }
    }
}
