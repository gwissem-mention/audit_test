<?php

namespace HopitalNumerique\PaiementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Facture controller.
 */
class FactureController extends Controller
{
    /**
     * Affiche la liste des Facture.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_paiement.grid.facture');

        return $grid->render('HopitalNumeriquePaiementBundle:Facture:index.html.twig');
    }

    /**
     * Affiche le formulaire d'édition de Facture.
     *
     * @param integer $id Id de Facture.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $facture = $this->get('hopitalnumerique_paiement.manager.facture')->findOneBy( array('id' => $id) );

        return $this->renderForm('hopitalnumerique_paiement_facture', $facture, 'HopitalNumeriquePaiementBundle:Facture:edit.html.twig' );
    }

    /**
     * Suppresion d'un Facture.
     * 
     * @param integer $id Id de Facture.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $facture = $this->get('hopitalnumerique_paiement.manager.facture')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_paiement.manager.facture')->delete( $facture );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_paiement_facture').'"}', 200);
    }

    








    /**
     * Effectue le render du formulaire Facture.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Facture   $entity   Entité $facture
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $facture, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $facture);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($facture->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_paiement.manager.facture')->save($facture);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Facture ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_paiement_facture') : $this->generateUrl('hopitalnumerique_paiement_facture_edit', array( 'id' => $facture->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'             => $form->createView(),
            'facture' => $facture
        ));
    }
}