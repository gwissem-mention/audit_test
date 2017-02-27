<?php

namespace Nodevo\RoleBundle\Manager;

use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\RoleBundle\Entity\Role;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Role.
 */
class RoleManager extends BaseManager
{
    protected $class = '\Nodevo\RoleBundle\Entity\Role';

    protected $roleList;

    public function __construct($em)
    {
        parent::__construct($em);
    }

    /**
     * Retourne la liste des roles correctement formatés pour la gestion des habilitations.
     *
     * @param array $datas Liste des roles non formatés
     *
     * @return array
     */
    public function reformateRolesForAcls($datas)
    {
        //prepare returned array
        $roles = new \stdClass();
        $roles->initiaux = [];
        $roles->nonInitiaux = [];

        //on construit 2 tableau (roles initiaux et roles non initiaux)
        foreach ($datas as $one) {
            if ($one->getInitial()) {
                $roles->initiaux[] = $one;
            } else {
                $roles->nonInitiaux[] = $one;
            }
        }

        return $roles;
    }

    /**
     * Override de la fonction save par défaut car on génère le code de l'utilisateur.
     *
     * @param Role $role
     */
    public function save($role)
    {
        $code = false;

        //GENERATE CODE
        if ($role->getRole() == '') {
            $code = 'ROLE_' . strtoupper(str_replace(' ', '_', $role->getName())) . '_';
            $role->setRole($code);
        }

        $this->em->persist($role);
        $this->em->flush();

        /*
         * Ajout de l'id en fin de code
         */
        if ($code !== false) {
            $role->setRole($code . $role->getId());
            $this->em->persist($role);
            $this->em->flush();
        }
    }

    /**
     * Retourne les roles sous forme de tableau.
     *
     * @return array
     */
    public function getRolesAsArray()
    {
        $datas = $this->findAll();
        $roles = [];
        foreach ($datas as $data) {
            $roles[$data->getRole()] = $data->getName();
        }

        return $roles;
    }

    /**
     * @param User|null $user
     *
     * @return string
     */
    public function getUserRole($user)
    {
        if ($user instanceof User) {
            //on récupère le rôle de l'user connecté
            $roles = $user->getRoles();
            $role = $roles[0];
        } else {
            $role = 'ROLE_ANONYME_10';
        }

        return $role;
    }

    /**
     * Retourne un tableau d'entités de roles en fonction du tableau des noms de role passés en param.
     *
     * @param array $nomsRoles Tableaux des noms de roles
     *
     * @return array[\Nodevo\RoleBundle\Entity\Role] Tableau des entités de roles correspondant
     */
    public function getRoleByArrayName(array $nomsRoles)
    {
        //Récupération de l'ensemble des rôles
        $datas = $this->findAll();
        $roles = [];
        //Récupération des rôles correspondant aux noms passés en param
        foreach ($datas as $data) {
            if (in_array($data->getRole(), $nomsRoles)) {
                $roles[] = $data;
            }
        }

        return $roles;
    }

    /**
     * Retourne les Roles d'un utilisateur.
     *
     * @param User $user
     *
     * @return Role[]
     */
    public function findByUser(User $user)
    {
        $roleLabels = $user->getRoles();

        return $this->findBy(['role' => $roleLabels]);
    }

    public function findAll()
    {
        if (null === $this->roleList) {
            $this->roleList = parent::findAll();
        }

        return $this->roleList;
    }
}
