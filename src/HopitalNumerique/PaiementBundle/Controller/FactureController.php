<?php

namespace HopitalNumerique\PaiementBundle\Controller;

use HopitalNumerique\PaiementBundle\Entity\Facture;
use HopitalNumerique\UserBundle\Entity\User;
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
     * Affiche le détail de la facture
     *
     * @param Facture $facture L'objet Facture à afficher
     */
    public function detailAction( Facture $facture)
    {
        return $this->render('HopitalNumeriquePaiementBundle:Facture:detail.html.twig', array(
            'facture' => $facture
        ));
    }

    /**
     * Partial : affiche le total de l'utilisateru dans sa fiche
     *
     * @param User $user L'utilisateur affiché
     */
    public function totalAction( User $user)
    {
        $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findBy( array('referent' => $user) );
        $formations    = array();
        $datas         = $this->get('hopitalnumerique_paiement.manager.remboursement')->calculPrice( $interventions, $formations );
        $total         = 0;

        foreach($datas as $data)
            $total += $data->total;

        return $this->render('HopitalNumeriquePaiementBundle:Facture:total.html.twig', array(
            'total' => $total
        ));
    }
}