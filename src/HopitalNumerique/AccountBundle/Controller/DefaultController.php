<?php

namespace HopitalNumerique\AccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        //On récupère l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();
        
        //get Flash messages visible for this user
        $messages = $this->get('hopitalnumerique_flash.manager.flash')->getMessagesForUser( $user );

        //get requetes
        $requetes = $this->get('hopitalnumerique_recherche.manager.requete')->getRequetesForDashboard( $user );

        //get Sessions
        $sessions = $this->get('hopitalnumerique_module.manager.session')->getSessionsForDashboard( $user );
        $sessionsFormateur = $this->get('hopitalnumerique_module.manager.session')->getSessionsForFormateurForDashboard( $user );

        //factures
        $factures = count($this->get('hopitalnumerique_paiement.manager.facture')->findBy( array('user' => $user, 'payee' => false ) ));

        //interventions
        $role = '';
        if( $user->hasRoleCmsi() ){
            $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findBy( array('cmsi' => $user ) );
            $role = 'CMSI';
        }
        elseif( $user->hasRoleAmbassadeur() ) {
            $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findBy( array('ambassadeur' => $user ) );
            $role = 'AMBASSADEUR';
        }
        else
            $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findBy( array('referent' => $user ) );

        //récupère la conf (l'ordre) des blocks du dashboard de l'user connecté
        $userConf = $this->buildDashboardRows( json_decode($user->getDashboardFront(), true) );

        return $this->render('HopitalNumeriqueAccountBundle:Default:index.html.twig', array(
            'messages'          => $messages,
            'requetes'          => $requetes,
            'sessions'          => $sessions,
            'factures'          => $factures,
            'sessionsFormateur' => $sessionsFormateur,
            'interventions'     => $interventions,
            'role'              => $role,
            'userConf'          => $userConf
        ));
    }

    /**
     * Enregistre l'ordre des blocks du dashboard front de l'utilisateur
     *
     * @param  Request $request La requete
     *
     * @return json
     */
    public function reorderAction(Request $request)
    {
        $datas = $request->request->get('datas');
        $dashboardFront = array();
        foreach($datas as $one)
            $dashboardFront[ $one['id'] ] = array( 'row' => $one['row'], 'col' => $one['col'] );
        
        //On récupère l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();
        $user->setDashboardFront( json_encode($dashboardFront) );
        $this->getDoctrine()->getManager()->flush();

        return new Response('{"success":true}', 200);
    }

















    /**
     * Construit le tableau du dashboard user
     *
     * @param array $dashboardFront Tableau de la config dashboard
     *
     * @return array
     */
    private function buildDashboardRows( $dashboardFront )
    {
        $datas                   = array();
        $datas[ 'messages' ]     = array( 'row' => 1, 'col' => 1);
        $datas[ 'requetes' ]     = array( 'row' => 1, 'col' => 2);
        $datas[ 'modules' ]      = array( 'row' => 2, 'col' => 1);
        $datas[ 'formateur' ]    = array( 'row' => 2, 'col' => 2);
        $datas[ 'intervention' ] = array( 'row' => 3, 'col' => 1);
        $datas[ 'factures' ]     = array( 'row' => 3, 'col' => 2);

        if( !is_null($dashboardFront) )
            $datas = array_replace($datas, $dashboardFront);
        
        return $datas;
    }
}