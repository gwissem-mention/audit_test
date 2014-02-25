<?php

namespace Nodevo\AclBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AclController extends Controller
{
    /**
     * Affiche la liste des Habilitations modifiables
     */
    public function indexAction()
    {
        $roles      = $this->get('nodevo_role.manager.role')->findAllOrdered('name');
        $ressources = $this->get('nodevo_acl.manager.ressource')->findAllOrdered();
        $acls       = $this->get('nodevo_acl.manager.acl')->getAsArray($ressources, $roles);
        $roles      = $this->get('nodevo_role.manager.role')->reformateRolesForAcls( $roles );

        return $this->render('NodevoAclBundle:Acl:index.html.twig', array( 'roles' => $roles, 'acls' => $acls, 'ressources' => $ressources ) );
    }

    /**
     * Action ajax appellée lors de la modification d'un acl
     */
    public function modifyAction()
    {
        //get AJAX vars
        $type      = $this->get('request')->request->get('type');
        $ressource = $this->get('request')->request->get('ressource');
        $role      = $this->get('request')->request->get('role');

        //get acl in database if exist, if not : add it
        $acl = $this->get('nodevo_acl.manager.acl')->findOneBy( array('ressource' => $ressource, 'role' => $role ) );
        if( $acl )
            $result = $this->get('nodevo_acl.manager.acl')->update( $acl, $type );
        else{
            $ressource = $this->get('nodevo_acl.manager.ressource')->findOneById($ressource);
            $role      = $this->get('nodevo_role.manager.role')->findOneById($role);
            
            $result = $this->get('nodevo_acl.manager.acl')->addNew( $ressource, $role, $type );
        }

        return new Response('{"success":true, "class":"'.($result ? 'btn-success' : 'btn-default').'"}', 200);
    }
}