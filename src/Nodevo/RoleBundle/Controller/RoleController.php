<?php
namespace Nodevo\RoleBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Role controller.
 */
class RoleController extends Controller
{
    /**
     * Lists all Role entities.
     */
    public function indexAction()
    {
        $grid = $this->get('nodevo_role.grid.role');

        return $grid->render('NodevoRoleBundle:Role:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de role
     */
    public function addAction()
    {
        $role = $this->get('nodevo_role.manager.role')->createEmpty();

        return $this->renderForm('nodevo_role_role', $role, 'NodevoRoleBundle:Role:edit.html.twig' );
    }

    /**
     * Affichage du formulaire de role
     * 
     * @param intger $id Identifiant du role
     */
    public function editAction( $id )
    {
        $role = $this->get('nodevo_role.manager.role')->findOneBy( array('id' => $id) );

        return $this->renderForm('nodevo_role_role', $role, 'NodevoRoleBundle:Role:edit.html.twig' );
    }

    /**
     * Affichage de la fiche d'un role
     * 
     * @param integer $id ID du role
     */
    public function showAction( $id )
    {
        $role = $this->get('nodevo_role.manager.role')->findOneBy( array('id' => $id) );

        return $this->render('NodevoRoleBundle:Role:show.html.twig', array(
            'role' => $role
        ));
    }

    /**
     * Suppression d'un role
     *
     * @param integer $id ID du role
     */
    public function deleteAction( $id )
    {
        $role = $this->get('nodevo_role.manager.role')->findOneBy( array('id' => $id) );

        if( $role->getInitial() ){
            $this->get('session')->getFlashBag()->add('danger', 'Il est interdit de supprimer un groupe initial.' );
        }else{
            $users = $this->get('hopitalnumerique_user.manager.user')->findUsersByRole( $role->getRole() );
            
            if( !is_null($users) ){
                $message = 'Ce groupe n\'a pas pu être supprimé car il a encore des utilisateurs associés.';
                $this->get('session')->getFlashBag()->add('danger', $message);
            }else{
                //suppression des acl pour ce role
                $acls = $this->get('nodevo_acl.manager.acl')->findBy( array('role' => $role) );
                foreach($acls as $acl)
                    $this->get('nodevo_acl.manager.acl')->delete( $acl );

                //Suppression du role
                $this->get('nodevo_role.manager.role')->delete( $role );
                $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );
            }
        }

        return new Response('{"success":true, "url" : "'.$this->generateUrl('nodevo_role_role').'"}', 200);
    }



    /**
     * Effectue le render du formulaire Role
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Role   $role     Entité role
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $role, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $role);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($role->getId()) ? true : false;

                // On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('nodevo_role.manager.role')->save($role);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Groupe ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                $do = $request->request->get('do');

                // On redirige vers la home page
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('nodevo_role_role') : $this->generateUrl('nodevo_role_edit', array( 'id' => $role->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form' => $form->createView(),
            'role' => $role
        ));
    }    
}