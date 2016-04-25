<?php

namespace Nodevo\MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    /**
     * Liste des items dans le menu
     *
     * @param integer $id Id du menu
     *
     * @return Vue en liste de tous les liens du menu $id
     */
    public function indexAction($id)
    {
        $menu = $this->get('nodevo_menu.manager.menu')->findOneById($id);
        $grid = $this->get('nodevo_menu.grid.item');
        $grid->setSourceCondition('menu', $id);

        return $grid->render('NodevoMenuBundle:Item:index.html.twig', array('menu' => $menu ));
    }

    /**
     * Affiche le formulaire d'ajout de liens de menu
     */
    public function addAction($id)
    {
        $item = $this->get('nodevo_menu.manager.item')->createEmpty();
        $menu = $this->get('nodevo_menu.manager.menu')->findOneById($id);
        $item->setMenu( $menu );

        return $this->renderForm('nodevo_menu_item', $item, 'NodevoMenuBundle:Item:edit.html.twig' );
    }

    /**
     * Affichage du formulaire de lien de menu
     * 
     * @param intger $id Identifiant du lien de menu
     */
    public function editAction( $id )
    {
        $item = $this->get('nodevo_menu.manager.item')->findOneBy( array('id' => $id) );

        return $this->renderForm('nodevo_menu_item', $item, 'NodevoMenuBundle:Item:edit.html.twig' );
    }

    /**
     * Suppression d'un utilisateur
     *
     * @param integer $id ID de l'utilisateur
     */
    public function deleteAction( $id )
    {
        $item = $this->get('nodevo_menu.manager.item')->findOneBy( array('id' => $id) );
        $menu = $item->getMenu();

        //Suppression de l'item
        $this->get('nodevo_menu.manager.item')->delete( $item );
        $this->get('nodevo_menu.manager.menu')->refreshTree( $menu );
        $this->container->get('nodevo_menu.dependency_injection.menu_cache')->deleteRenderByAlias($menu->getAlias());

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('nodevo_menu_item', array('id'=>$menu->getId())).'"}', 200);
    }







        


  
    



    /**
     * Effectue le render du formulaire Item
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Item   $item     Entité item
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $item, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $item);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {   
                
                //test ajout ou edition
                $new = is_null($item->getId()) ? true : false;

                //on manipule les paramètres de la route
                $item->setRouteParameters( $this->getPostRouteParametres( $request ) );

                // On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('nodevo_menu.manager.item')->save($item);
                $this->get('nodevo_menu.manager.item')->updateOrder( $item );

                // Menu
                $this->get('nodevo_menu.manager.menu')->getTree( $item->getMenu(), true );
                $this->container->get('nodevo_menu.dependency_injection.menu_cache')->deleteRenderByAlias($item->getMenu()->getAlias());

                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Element ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                $do = $request->request->get('do');

                if( $do == "save-close" )
                    $url = $this->generateUrl('nodevo_menu_item', array('id' => $item->getMenu()->getId()) );
                else
                    $url = $this->generateUrl('nodevo_menu_item_edit', array('id' => $item->getId()) );

                return $this->redirect( $url );
            }
        }

        return $this->render( $view , array(
            'form' => $form->createView(),
            'item' => $item
        ));
    }

    /**
     * Retourne les paramètres de route saisis par l'utilisateur lors de l'édition d'un lien de menu.
     * 
     * @return array Tableau associatif NomParametre => ValeurParametre
     */
    private function getPostRouteParametres( $request )
    {
        $datas           = $request->request->get('nodevo_menu_item');
        $routeParameters = isset($datas['routeParameters']) ? $datas['routeParameters'] : array();
        $params          = array();

        foreach($routeParameters as $key => $val)
            $params[ str_replace('routeParameters_', '', $key) ] = $val;

        return json_encode($params);
    }
}
