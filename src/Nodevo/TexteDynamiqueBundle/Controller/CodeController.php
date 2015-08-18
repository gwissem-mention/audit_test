<?php

namespace Nodevo\TexteDynamiqueBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Code controller.
 */
class CodeController extends Controller
{
    /**
     * Affiche la liste des Code.
     */
    public function indexAction()
    {
        $grid = $this->get('nodevo_textedynamique.grid.code');

        return $grid->render('NodevoTexteDynamiqueBundle:Code:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de Code.
     */
    public function addAction()
    {
        $code = $this->get('nodevo_textedynamique.manager.code')->createEmpty();

        return $this->renderForm('nodevo_textedynamique_code', $code, 'NodevoTexteDynamiqueBundle:Code:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Code.
     *
     * @param integer $id Id de Code.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $code = $this->get('nodevo_textedynamique.manager.code')->findOneBy( array('id' => $id) );

        return $this->renderForm('nodevo_textedynamique_code', $code, 'NodevoTexteDynamiqueBundle:Code:edit.html.twig' );
    }

    /**
     * Suppresion d'un Code.
     * 
     * @param integer $id Id de Code.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $code = $this->get('nodevo_textedynamique.manager.code')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('nodevo_textedynamique.manager.code')->delete( $code );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('nodevo_textedynamique_admin_texte-dynamique_').'"}', 200);
    }





    /**
     * Effectue le render du formulaire Code.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Code   $entity   Entité $code
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $code, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $code);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($code->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('nodevo_textedynamique.manager.code')->save($code);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Code ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('nodevo_textedynamique_code') : $this->generateUrl('nodevo_textedynamique_code_edit', array( 'id' => $code->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'             => $form->createView(),
            'code' => $code
        ));
    }
}