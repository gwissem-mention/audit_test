<?php

namespace HopitalNumerique\AccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * Index Action.
     */
    public function indexAction()
    {
        //On récupère l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //get Flash messages visible for this user
        $messages = $this->get('hopitalnumerique_flash.manager.flash')->getMessagesForUser($user);

        //get Sessions
        $sessions = $this->get('hopitalnumerique_module.manager.session')->getSessionsForDashboard($user);
        $sessionsFormateur = $this->get('hopitalnumerique_module.manager.session')->getSessionsForFormateurForDashboard($user);

        //factures
        $factures = count($this->get('hopitalnumerique_paiement.manager.facture')->findBy(['user' => $user, 'payee' => false]));

        //interventions
        $role = '';
        if ($user->hasRoleCmsi()) {
            $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findBy(['cmsi' => $user]);
            $role = 'CMSI';
        } elseif ($user->hasRoleAmbassadeur()) {
            $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findBy(['ambassadeur' => $user]);
            $role = 'AMBASSADEUR';
        } else {
            $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findBy(['referent' => $user]);
        }

        //récupère la conf (l'ordre) des blocks du dashboard de l'user connecté
        $userConf = $this->buildDashboardRows(json_decode($user->getDashboardFront(), true));

        return $this->render('HopitalNumeriqueAccountBundle:Default:index.html.twig', [
            'messages' => $messages,
            'sessions' => $sessions,
            'factures' => $factures,
            'sessionsFormateur' => $sessionsFormateur,
            'interventions' => $interventions,
            'role' => $role,
            'userConf' => $userConf,
        ]);
    }

    /**
     * Enregistre l'ordre des blocks du dashboard front de l'utilisateur.
     *
     * @param Request $request La requete
     *
     * @return json
     */
    public function reorderAction(Request $request)
    {
        $datas = $request->request->get('datas');
        $dashboardFront = [];
        foreach ($datas as $one) {
            $dashboardFront[$one['id']] = ['row' => $one['row'], 'col' => $one['col']];
        }

        //On récupère l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();
        $user->setDashboardFront(json_encode($dashboardFront));
        $this->getDoctrine()->getManager()->flush();

        return new Response('{"success":true}', 200);
    }

    /**
     * Construit le tableau du dashboard user.
     *
     * @param array $dashboardFront Tableau de la config dashboard
     *
     * @return array
     */
    private function buildDashboardRows($dashboardFront)
    {
        $datas = [];
        $datas['messages'] = ['row' => 1, 'col' => 1];
        $datas['modules'] = ['row' => 2, 'col' => 1];
        $datas['formateur'] = ['row' => 2, 'col' => 2];
        $datas['intervention'] = ['row' => 3, 'col' => 1];
        $datas['factures'] = ['row' => 3, 'col' => 2];

        if (!is_null($dashboardFront)) {
            $datas = array_replace($datas, $dashboardFront);
        }

        return $datas;
    }
}
