<?php

namespace Nodevo\FaqBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Faq controller.
 */
class FaqController extends Controller
{
    /**
     * Affiche la liste des Faq.
     */
    public function indexAction()
    {
        $grid = $this->get('nodevo_faq.grid.faq');

        return $grid->render('NodevoFaqBundle:Faq:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de Faq.
     */
    public function addAction()
    {
        $faq = $this->get('nodevo_faq.manager.faq')->createEmpty();

        return $this->_renderForm('nodevo_faq_faq', $faq, 'NodevoFaqBundle:Faq:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Faq.
     *
     * @param integer $id Id de Faq.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $faq = $this->get('nodevo_faq.manager.faq')->findOneBy( array('id' => $id) );

        return $this->_renderForm('nodevo_faq_faq', $faq, 'NodevoFaqBundle:Faq:edit.html.twig' );
    }

    /**
     * Affiche le Faq en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de Faq.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $faq = $this->get('nodevo_faq.manager.faq')->findOneBy( array( 'id' => $id) );

        return $this->render('NodevoFaqBundle:Faq:show.html.twig', array(
            'faq' => $faq,
        ));
    }

    /**
     * Suppresion d'un Faq.
     * 
     * @param integer $id Id de Faq.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $faq = $this->get('nodevo_faq.manager.faq')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('nodevo_faq.manager.faq')->delete( $faq );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('nodevo_faq_faq').'"}', 200);
    }





    /**
     * Effectue le render du formulaire Faq.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Faq   $entity   Entité $faq
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function _renderForm( $formName, $faq, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $faq);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($faq->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('nodevo_faq.manager.faq')->save($faq);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Faq ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('nodevo_faq_faq') : $this->generateUrl('nodevo_faq_faq_edit', array( 'id' => $faq->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'             => $form->createView(),
            'faq' => $faq
        ));
    }
}