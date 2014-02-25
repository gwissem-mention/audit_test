<?php

namespace Nodevo\MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends Controller
{
    /**
     * Affichage des menus
     */
    public function indexAction()
    {
        $grid = $this->get('nodevo_menu.grid.menu');

        return $grid->render('NodevoMenuBundle:Menu:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout du menu
     */
    public function addAction()
    {
        $menu = $this->get('nodevo_menu.manager.menu')->createEmpty();

        return $this->_renderForm('nodevo_menu_menu', $menu, 'NodevoMenuBundle:Menu:edit.html.twig' );
    }

    /**
     * Affichage du formulaire de menu
     * 
     * @param intger $id Identifiant du menu
     */
    public function editAction( $id )
    {
        //Récupération de l'menu passé en param
        $menu = $this->get('nodevo_menu.manager.menu')->findOneBy( array('id' => $id) );

        return $this->_renderForm('nodevo_menu_menu', $menu, 'NodevoMenuBundle:Menu:edit.html.twig' );
    }

    /**
     * Suppression d'un menu
     *
     * @param integer $id ID du menu
     */
    public function deleteAction( $id )
    {
        $menu = $this->get('nodevo_menu.manager.menu')->findOneBy( array('id' => $id) );

        if ( !$menu->getLock() ) {
            //Suppression de l'utilisateur
            $this->get('nodevo_menu.manager.menu')->delete( $menu );
            $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );
        }else
            $this->get('session')->getFlashBag()->add('warning', 'La suppression d\'un menu vérouillé est interdit.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('nodevo_menu_menu').'"}', 200);
    }




    /**
     * Effectue le render du formulaire Menu
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Menu   $menu   Entité Menu
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function _renderForm( $formName, $menu, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $menu);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($menu->getId()) ? true : false;

                // On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('nodevo_menu.manager.menu')->save($menu);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Menu ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                $do = $request->request->get('do');

                // On redirige vers la home page
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('nodevo_menu_menu') : $this->generateUrl('nodevo_menu_menu_edit', array( 'id' => $menu->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form' => $form->createView(),
            'menu' => $menu
        ));
    }
}