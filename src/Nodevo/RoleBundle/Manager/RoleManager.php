<?php

namespace Nodevo\RoleBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;

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
     * Retourne les données sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {
        return $this->getRepository()->getDatasForGrid( $condition );
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
            $roles[ $data->getRole() ] = $data->getName();

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
}
