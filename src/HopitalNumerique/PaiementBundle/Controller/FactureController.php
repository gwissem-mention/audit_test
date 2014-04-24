<?php

namespace HopitalNumerique\PaiementBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     * Affiche le Facture en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de Facture.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $facture = $this->get('hopitalnumerique_paiement.manager.facture')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriquePaiementBundle:Facture:show.html.twig', array(
            'facture' => $facture,
        ));
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

    public function suiviAction()
    {
        //On récupère l'user connecté
        $user          = $this->get('security.context')->getToken()->getUser();
        $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getForFactures( $user );

        return $this->render('HopitalNumeriquePaiementBundle:Facture:suivi.html.twig', array(
            'interventions' => $interventions
        ));
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