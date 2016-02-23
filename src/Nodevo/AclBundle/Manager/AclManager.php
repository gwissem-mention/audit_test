<?php

namespace Nodevo\AclBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Nodevo\AclBundle\Entity\Acl;
use Nodevo\AclBundle\Manager\RessourceManager;
use Nodevo\RoleBundle\Manager\RoleManager;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\ApcCache;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Manager de l'entité Acl
 * 
 * @author Quentin SOMAZZI
 */
class AclManager extends BaseManager
{
    protected   $_class = '\Nodevo\AclBundle\Entity\Acl';
    private     $_ressourceManager;
    private     $_roleManager;
    private     $_writeWords = array();

    /**
     * Construct extension : we need to have the Container here
     *
     * @param EntityManager         $em                 Entity Mangager de doctrine
     * @param RessourceManager      $ressourceManager   Manager des ressources
     * @param RoleManager           $roleManager        Manager des roles
     */
    public function __construct( EntityManager $em, RessourceManager $ressourceManager, RoleManager $roleManager, $options = array() )
    {
        parent::__construct($em);

        $this->_ressourceManager = $ressourceManager;
        $this->_roleManager      = $roleManager;

        $this->setOptions($options);
    }

    /**
     * Retourne les ACL sous forme de tableau correctement formaté
     *
     * @param array $ressources Liste des ressources
     * @param array $roles      Liste des roles
     *
     * @return array
     */
    public function getAsArray($ressources, $roles)
    {
        //default datas : on récupère dans un premier temps TOUTES les acls ( et on authorise ni la lecture, ni l'écriture )
        //sauf pour le Super Admin
        $acls = array();
        foreach ($ressources as $ressource)
        {
            $acls[ $ressource->getId() ] = array();

            foreach( $roles as $role )
            {
                $acls[ $ressource->getId() ][ $role->getId() ] = array();
                $acls[ $ressource->getId() ][ $role->getId() ]['read']  = $role->getId() == 1 ? 1 : 0;
                $acls[ $ressource->getId() ][ $role->getId() ]['write'] = $role->getId() == 1 ? 1 : 0;
            }
        }

        //overide datas from database : on met la bonne valeur de lecture et d'écriture selon les données récupérées en base
        $results = $this->getRepository()->getAcls()->getResult();
        foreach ($results as $result)
        {
            $acls[ $result['ressource'] ][ $result['role'] ]['read']  = $result['read'];
            $acls[ $result['ressource'] ][ $result['role'] ]['write'] = $result['write'];
        }

        return $acls;
    }

    /**
     * Met à jour un acl selon le type
     *
     * @param Acl     $acl  Une entrée d'Acl
     * @param boolean $type Type read = 1, Type write = 2
     *
     * @return boolean
     */
    public function update($acl, $type)
    {
        //si on à choisis la lecture
        if( $type == 1 ){
            //selon la valeur actuelle en base, on met l'inverse
            $result = $acl->getRead() ? false : true;
            $acl->setRead( $result );
            //si on enlève l'accès en lecture, on enlève aussi l'accès en écriture
            if(!$result)
                $acl->setWrite( false );
        }
        //si on à choisis l'écriture
        else 
        {
            //selon la valeur actuelle en base, on met l'inverse
            $result = $acl->getWrite() ? false : true;
            $acl->setWrite( $result );
            //si on donne directement l'accès en écriture, on donne aussi l'accès en lecture
            if( $acl->getRessource()->getType() == 2 ){
                $acl->setRead( $result );
            } elseif($result){
                $acl->setRead( true );
            }
        }

        $this->save( $acl );
        return $result;
    }

    /**
     * Ajoute une nouvel Acl
     *
     * @param Ressource $ressource La ressource concernée
     * @param Role      $role      Le rôle concerné
     * @param boolean   $type      Type read = 1, write = 2
     */
    public function addNew($ressource, $role, $type)
    {
        $acl = new Acl();
        $acl->setRessource( $ressource );
        $acl->setRole( $role );

        if( $type == 1 ){
            $acl->setRead( true );
            $acl->setWrite( false );
        }else{
            $acl->setWrite( true );
            $acl->setRead( true );
        }

        $this->save( $acl );

        return true;
    }

    /**
     * Fonction qui vérifie si l'url à accès à la route demandée
     *
     * @param string  $url  Nom de l'url
     * @param User    $user Utilisateur connecté
     *
     * @return VoterInterface
     */
    public function checkAuthorization( $url, $user )
    {
        $cacheDriver = new ApcCache();

        if( $url === '#' || $url === '/' || substr($url, 0, 11) === 'javascript:')
            return VoterInterface::ACCESS_GRANTED;

        if ($cacheDriver->contains("_acl_roles"))
        {
            $rolesByRole = $cacheDriver->fetch("_acl_roles");
        }
        else
        {
            //Récupération des roles par 'role'
            $roles = $this->_roleManager->findAll();
            $rolesByRole = array();

            foreach ($roles as $role) 
            {
                $rolesByRole[$role->getRole()] = $role;
            }

            $cacheDriver->save("_acl_roles", $rolesByRole, null);
        }

        if( $user === 'anon.' )
        {
            $roles = $this->getRolesOfUser(array('ROLE_ANONYME_10'), $rolesByRole);
        }
        else{
            if( $user->hasRole('ROLE_ADMINISTRATEUR_1') )
                return VoterInterface::ACCESS_GRANTED;

            $roles = $this->getRolesOfUser($user->getRoles(), $rolesByRole);
        }
        
        //search in DB ressource matching this url
        $ressource = $this->_ressourceManager->getRessourceMatchingUrl( $url );

        //si on ne match aucune ressource, on bloque l'accès.
        if( is_null($ressource) )
            return VoterInterface::ACCESS_DENIED;

        //search if Acl matching role and Ressource exist
        if ($cacheDriver->contains("_acl_acls"))
        {
            $acls = $cacheDriver->fetch("_acl_acls");
        }
        else
        {
            $acls = $this->getAclByRessourceByRole();
            $cacheDriver->save("_acl_acls", $acls, null);
        }

        if(count($acls) > 0
            && array_key_exists($roles[0]->getId(), $acls)
            && array_key_exists($ressource->getId(), $acls[$roles[0]->getId()]))
        {
            $acl = $acls[$roles[0]->getId()][$ressource->getId()];
        }
        else
        {
            $acl = null;
        }

        //if no acl matching exist, access denied
        if ( is_null($acl) )
            return VoterInterface::ACCESS_DENIED;

        $wordFind = false;

        //check Write access
        $writeWords = array_merge(array('create', 'edit', 'delete', 'modify', 'new', 'update', 'add'), $this->_writeWords);
        foreach($writeWords as $word) {
            if( strpos($url, $word) )
                $wordFind = true;
        }

        if( $wordFind )
            return $acl->getWrite() ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;

        return $acl->getRead() ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    public function getAclByRessourceByRole()
    {
        $acls = $this->findAll();
        $aclsOrdered = array();

        foreach ($acls as $acl) 
        {
            if(!array_key_exists($acl->getRole()->getId(), $aclsOrdered))
            {
                $aclsOrdered[$acl->getRole()->getId()] = array();
            }

            $aclsOrdered[$acl->getRole()->getId()][$acl->getRessource()->getId()] = $acl;
        }

        return $aclsOrdered;
    }







    private function getRolesOfUser($rolesOfUsers , $roles)
    {
        $rolesUsers = array();

        foreach ($rolesOfUsers as $roleUser) 
        {
            if(array_key_exists($roleUser, $roles))
            {
                $rolesUsers[] = $roles[$roleUser];
            }
        }

        return $rolesUsers;
    }

    /**
     * Gère les options passées en paramètre
     *
     * @param options Tableau d'options
     */
    private function setOptions($options = array())
    {
        if (isset($options['writeWords']) && is_array($options['writeWords']))
            $this->_writeWords = $options['writeWords'];
        else
            $this->_writeWords = array();
    }
}
