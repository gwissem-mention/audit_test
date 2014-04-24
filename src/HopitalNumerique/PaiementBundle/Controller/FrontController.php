<?php

namespace HopitalNumerique\PaiementBundle\Controller;

use HopitalNumerique\PaiementBundle\Entity\Facture;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Front controller.
 */
class FrontController extends Controller
{
    /**
     * Interface de suivi des paiements en front
     *
     * @return view
     */
    public function suiviAction()
    {
        //On récupère l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //get interventions + formations
        $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getForFactures( $user );
        $formations    = array();

        $datas = $this->get('hopitalnumerique_paiement.manager.remboursement')->calculPrice( $interventions, $formations );

        return $this->render('HopitalNumeriquePaiementBundle:Front:suivi.html.twig', array(
            'datas' => $datas
        ));
    }

    /**
     * Enregistre la facture
     *
     * @return Redirect
     */
    public function createFactureAction(Request $request)
    {
        $interventions = $request->request->get('intervention');
        $formations    = $request->request->get('formation');

        if( is_null($interventions) && is_null($formations) ){
            $this->get('session')->getFlashBag()->add( 'warning' , 'Merci de sélectioner au moins 1 ligne' );
            return $this->redirect( $this->generateUrl('hopitalnumerique_paiement_front') );
        }

        //create object facture
        $facture = new Facture;
        $this->get('hopitalnumerique_paiement.manager.facture')->save($facture);

        //prepare ref
        $statutRemboursement = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id'=>6));

        //handle interventions
        $toSave = array();
        foreach($interventions as $id => $prix) {
            $intervention = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findOneBy( array('id' => $id) );
            $intervention->setFacture( $facture );
            $intervention->setRemboursementEtat( $statutRemboursement );
            $intervention->setTotal( $prix );
            
            $toSave[] = $intervention;
        }
        $this->get('hopitalnumerique_intervention.manager.intervention_demande')->save($toSave);

        //handle formations

        $this->get('session')->getFlashBag()->add( 'success' , 'Facture générée avec succès' );     
        return $this->redirect( $this->generateUrl('hopitalnumerique_paiement_front') );
    }
}
