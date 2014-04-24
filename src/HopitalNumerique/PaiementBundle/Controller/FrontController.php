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
        $datas         = $this->get('hopitalnumerique_paiement.manager.remboursement')->calculPrice( $interventions, $formations );

        //get Factures
        $factures = $this->get('hopitalnumerique_paiement.manager.facture')->findBy( array('user' => $user) );

        return $this->render('HopitalNumeriquePaiementBundle:Front:suivi.html.twig', array(
            'datas'    => $datas,
            'factures' => $factures
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

        //On récupère l'user connecté
        $user    = $this->get('security.context')->getToken()->getUser();
        $facture = $this->get('hopitalnumerique_paiement.manager.facture')->createFacture($user, $interventions, $formations);

        //Generate PDF
        $code = $facture->getUser()->getId() . $facture->getId();
        $this->get('knp_snappy.pdf')->generateFromHtml(
            $this->renderView( 'HopitalNumeriquePaiementBundle:Pdf:facture.html.twig', array(
                'code'          => $code,
                'facture'       => $facture,
                'formations'    => array()
            )),
            __ROOT_DIRECTORY__ . '/files/factures/facture'.$code.'.pdf',
            array('margin-bottom' => 10, 'margin-left' => 4, 'margin-right' => 4, 'margin-top' => 10, 'encoding' => 'UTF-8')
        );

        //save file Name
        $facture->setName( 'facture' . $code . '.pdf' );
        $this->get('hopitalnumerique_paiement.manager.facture')->save( $facture );

        $this->get('session')->getFlashBag()->add( 'success' , 'Facture générée avec succès' );     
        return $this->redirect( $this->generateUrl('hopitalnumerique_paiement_front') );
    }




    /**
     * Génère la facture
     *
     * @return Pdf
     */
    public function exportAction( Facture $facture )
    {
        $code = $facture->getUser()->getId() . $facture->getId();

        return $this->render( 'HopitalNumeriquePaiementBundle:Pdf:facture.html.twig', array(
            'code'          => $code,
            'facture'       => $facture,
            'formations'    => array()
        ));
    }
}
