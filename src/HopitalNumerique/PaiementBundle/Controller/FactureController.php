<?php

namespace HopitalNumerique\PaiementBundle\Controller;

use HopitalNumerique\PaiementBundle\Entity\Facture;
use HopitalNumerique\UserBundle\Entity\User;
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
     * Paye la facture et redirige l'admin sur la vue liste
     *
     * @param Facture $facture L'objet Facture à payer
     */
    public function payeAction( Facture $facture)
    {
        $this->get('hopitalnumerique_paiement.manager.facture')->paye( $facture );

        $this->get('session')->getFlashBag()->add( 'success', 'Facture payée' );
        return $this->redirect( $this->generateUrl('hopitalnumerique_paiement_facture') );
    }

    /**
     * Annule la facture et redirige l'admin sur la vue liste
     *
     * @param Facture $facture L'objet Facture à annuler
     */
    public function etatAction( Facture $facture)
    {
        $etat = $this->get('hopitalnumerique_paiement.manager.facture')->changeEtat( $facture );
        if($etat){
            $this->get('session')->getFlashBag()->add( 'success', 'Facture annulée' );

        } else {
            $this->get('session')->getFlashBag()->add('success', 'Facture désannulée');
        }
        return $this->redirect( $this->generateUrl('hopitalnumerique_paiement_facture') );
    }

    /**
     * Affiche le détail de la facture
     *
     * @param Facture $facture L'objet Facture à afficher
     */
    public function detailAction( Facture $facture)
    {
        if ($facture->hasBeenCanceled()) {
            $interventions = $facture->getFactureAnnulee()->getInterventions();
            $formations = $facture->getFactureAnnulee()->getFormations();
        } else {
            $formations = $this->get('hopitalnumerique_module.manager.inscription')->getInscriptionsForFactureOrdered($facture->getId());
            $interventions = $facture->getInterventions();
        }

        return $this->render('HopitalNumeriquePaiementBundle:Facture:detail.html.twig', array(
            'facture' => $facture,
            'interventions' => $interventions,
            'formations' => $formations
        ));
    }

    /**
     * Partial : affiche le total de l'utilisateur dans sa fiche
     *
     * @param User $user L'utilisateur affiché
     */
    public function totalAction( User $user)
    {
        $total = 0;

        if( $user->getRole() != 'ROLE_ADMINISTRATEUR_1') {
            $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getForTotal( $user );
            $formations    = $this->get('hopitalnumerique_module.manager.inscription')->getForFactures( $user );
            $datas         = $this->get('hopitalnumerique_paiement.manager.remboursement')->calculPrice( $interventions, $formations );
            
            foreach($datas as $data)
            {
                $total += $data->total['prix'];    
            }
        }

        return $this->render('HopitalNumeriquePaiementBundle:Facture:total.html.twig', array(
            'total' => $total
        ));
    }

    /**
     * Exporte la facture
     *
     * @return Pdf
     */
    public function exportAction( Facture $facture )
    {
        $options = array(
            'serve_filename' => $facture->getName(),
            'absolute_path'  => false,
            'inline'         => false,
        );
        
        $fileName = __ROOT_DIRECTORY__.'/files/factures/' . $facture->getName();
    
        if( file_exists($fileName) ) {
            return $this->get('igorw_file_serve.response_factory')->create( $fileName, 'application/pdf', $options);
        } else {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add( ('danger') , 'Le document n\'existe plus sur le serveur.' );

            return $this->redirect( $this->generateUrl('hopitalnumerique_paiement_facture') );
        }
    }

    /**
     * [regenerateAction description]
     *
     * @param  Facture $facture [description]
     *
     * @return [type]
     */
    public function regenerateAction( Facture $facture )
    {
        //On récupère l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //get Reponses
        $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( 2 , $user->getId(), true );
        $infos    = $this->get('hopitalnumerique_paiement.manager.facture')->formateInfos( $reponses );
        $code     = $facture->getUser()->getId() . $facture->getId();

        //delete old file
        if( file_exists(__ROOT_DIRECTORY__ . '/files/factures/facture'.$code.'.pdf') )
            unlink(__ROOT_DIRECTORY__ . '/files/factures/facture'.$code.'.pdf');

        //Generate PDF
        $this->get('knp_snappy.pdf')->generateFromHtml(
            $this->renderView( 'HopitalNumeriquePaiementBundle:Pdf:facture.html.twig', array(
                'code'       => $code,
                'facture'    => $facture,
                'infos'      => $infos,
                'formations' => array()
            )),
            __ROOT_DIRECTORY__ . '/files/factures/facture'.$code.'.pdf',
            array('margin-bottom' => 10, 'margin-left' => 4, 'margin-right' => 4, 'margin-top' => 10, 'encoding' => 'UTF-8')
        );

        //save file Name
        $facture->setName( 'facture' . $code . '.pdf' );
        $this->get('hopitalnumerique_paiement.manager.facture')->save( $facture );

        $this->get('session')->getFlashBag()->add( 'success' , 'Facture re-générée avec succès' );
        return $this->redirect( $this->generateUrl('hopitalnumerique_paiement_facture') );
    }

    /**
     * Annule la facture.
     *
     * @param \HopitalNumerique\PaiementBundle\Entity\Facture $facture Facture
     */
    public function cancelAction(Facture $facture)
    {
        if (!$facture->hasBeenCanceled()) {
            $this->container->get('hopitalnumerique_paiement.manager.facture')->cancel($facture);
            $this->addFlash('success', 'Facture Abandonnée.');
        }

        return $this->redirect($this->generateUrl('hopitalnumerique_paiement_facture'));
    }
}
