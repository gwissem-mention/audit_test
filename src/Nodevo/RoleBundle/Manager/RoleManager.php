<?php

namespace Nodevo\RoleBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Role
 */
class RoleManager extends BaseManager
{
    protected $_class = '\Nodevo\RoleBundle\Entity\Role';

    public function __construct($em)
    {
        parent::__construct($em);
    }

    /**
     * Retourne la liste des roles correctement formatés pour la gestion des habilitations
     *
     * @param array $datas Liste des roles non formatés
     *
     * @return array
     */
    public function reformateRolesForAcls( $datas )
    {
        //prepare returned array
        $roles              = new \stdClass;
        $roles->initiaux    = array();
        $roles->nonInitiaux = array();

        //on construit 2 tableau (roles initiaux et roles non initiaux)
        foreach($datas as $one){
            if( $one->getInitial() )
                $roles->initiaux[] = $one;
            else
                $roles->nonInitiaux[] = $one;
        }

        return $roles;
    }

    /**
     * Override de la fonction save par défaut car on génère le code de l'utilisateur
     *
     * @param Role $entity Entité role
     *
     * @return empty
     */
    public function save( $role )
    {
        $code = false;

        //GENERATE CODE
        if($role->getRole() == '') {
            $code = 'ROLE_' . strtoupper( str_replace(' ', '_', $role->getName()) ) . '_';
            $role->setRole($code);
        }

        $this->_em->persist($role);
        $this->_em->flush();

        /**
         * Ajout de l'id en fin de code
         */
        if( $code !== false){
            $role->setRole($code . $role->getId() );
            $this->_em->persist($role);
            $this->_em->flush();
        }
    }

    /**
     * Retourne les roles sous forme de tableau
     *
     * @return array
     */
    public function getRolesAsArray()
    {
        $datas = $this->findAll();
        $roles = array();
        foreach($datas as $data)
        {
            $roles[ $data->getRole() ] = $data->getName();
        }

        return $roles;
    }

    /**
     * Retourne le Role du user
     *
     * @param $user Utilisateur : soit une string, soit un objet FOS\UserBundle\Entity\User
     *
     * @return string
     */
    public function getUserRole($user)
    {
        if( $user === 'anon.' )
            $role = 'ROLE_ANONYME_10';
        else
        {
            //on récupère le rôle de l'user connecté
            $roles = $user->getRoles();
            $role  = $roles[0];
        }

        return $role;
    }

    /**
     * Retourne un tableau d'entités de roles en fonction du tableau des noms de role passés en param
     * 
     * @param array $nomsRoles Tableaux des noms de roles
     * 
     * @return array[\Nodevo\RoleBundle\Entity\Role] Tableau des entités de roles correspondant
     */
    public function getRoleByArrayName(array $nomsRoles )
    {
        //Récupération de l'ensemble des rôles
        $datas = $this->findAll();
        $roles = array();
        //Récupération des rôles correspondant aux noms passés en param
        foreach($datas as $data)
        {
            if(in_array($data->getRole(), $nomsRoles))
            {
                $roles[] = $data;
            }
        }
        
        return $roles;
    }
}
