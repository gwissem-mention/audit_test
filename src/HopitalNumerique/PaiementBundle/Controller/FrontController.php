<?php

namespace HopitalNumerique\PaiementBundle\Controller;

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

    public function createFactureAction(Request $request)
    {
        $datas = $request->request->get('intervention');

        echo '<pre>';
        var_dump($datas);
        die();
    }
}
