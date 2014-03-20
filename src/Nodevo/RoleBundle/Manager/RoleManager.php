<?php

namespace Nodevo\RoleBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Manager de l'entité Role
 * 
 * @author Quentin SOMAZZI
 */
class RoleManager extends BaseManager
{
    protected $_class = '\Nodevo\RoleBundle\Entity\Role';
    protected $_securityContext;

    public function __construct($em, $securityContext)
    {
        parent::__construct($em);
        $this->_securityContext = $securityContext;
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
         * @todo  : pas super propre, à revoir surement
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
     * Retourne le Role de l'user connecté
     *
     * @return string
     */
    public function getConnectedUserRole()
    {
        $user  = $this->_securityContext->getToken()->getUser();
        if( $user === 'anon.')
            $role = 'ROLE_ANONYME_10';
        else{
            //on récupère le rôle de l'user connecté
            $roles = $user->getRoles();
            $role  = $roles[0];
        }

        return $role;
    }
}
