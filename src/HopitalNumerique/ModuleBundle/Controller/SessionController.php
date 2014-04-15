<?php

namespace HopitalNumerique\ModuleBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Session controller.
 */
class SessionController extends Controller
{
    /**
     * Affiche la liste des Session.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_module.grid.session');

        return $grid->render('HopitalNumeriqueModuleBundle:Session:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de Session.
     */
    public function addAction()
    {
        $session = $this->get('hopitalnumerique_module.manager.session')->createEmpty();

        return $this->renderForm('hopitalnumerique_module_session', $session, 'HopitalNumeriqueModuleBundle:Session:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Session.
     *
     * @param integer $id Id de Session.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $session = $this->get('hopitalnumerique_module.manager.session')->findOneBy( array('id' => $id) );

        return $this->renderForm('hopitalnumerique_module_session', $session, 'HopitalNumeriqueModuleBundle:Session:edit.html.twig' );
    }

    /**
     * Affiche le Session en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de Session.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $session = $this->get('hopitalnumerique_module.manager.session')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueModuleBundle:Session:show.html.twig', array(
            'session' => $session,
        ));
    }

    /**
     * Suppresion d'un Session.
     * 
     * @param integer $id Id de Session.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $session = $this->get('hopitalnumerique_module.manager.session')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_module.manager.session')->delete( $session );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_module_module_session').'"}', 200);
    }





    /**
     * Effectue le render du formulaire Session.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Session   $entity   Entité $session
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $session, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $session);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($session->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_module.manager.session')->save($session);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Session ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_module_module_session') : $this->generateUrl('hopitalnumerique_module_module_session_edit', array( 'id' => $session->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'             => $form->createView(),
            'session' => $session
        ));
    }
}