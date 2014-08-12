<?php
namespace HopitalNumerique\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nodevo\ToolsBundle\Tools\Chaine;

/**
 * 
 */
class DefaultController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        //On récupère l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //récupère la conf (l'ordre) des blocks du dashboard de l'user connecté
        $userConf = $this->buildDashboardRows( json_decode($user->getDashboardBack(), true) );

        //Initialisation des blocs
        $blocUser          = $this->getBlockuser();
        $blocObjets        = $this->getBlockObjets();
        $blocForum         = $this->getBlockForum();
        $blocInterventions = array( 'demandees' => 0, 'attente' => 0, 'en-cours' => 0, 'refusees' => 0, 'annulees' => 0 );
        $blocSessions      = array( 'inscriptions' => 0, 'next' => array() );
        $blocPaiements     = array( 'apayer' => 0, 'attente' => 0, 'janvier' => 0 );

        //Bloc Interventions
        $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findAll();
        foreach($interventions as $intervention){
            $etat = $intervention->getInterventionEtat()->getId();

            if( $etat == 14 || $etat == 17 || $etat == 18 || $etat == 19)
                $blocInterventions['demandees']++;
            elseif( $etat == 15 )
                $blocInterventions['attente']++;
            elseif( $etat == 21 )
                $blocInterventions['en-cours']++;
            elseif( $etat == 16 || $etat == 20 )
                $blocInterventions['refusees']++;
            elseif( $etat == 309 )
                $blocInterventions['annulees']++;
        }
        
        //Bloc Sessions
        $blocSessions['next'] = $this->get('hopitalnumerique_module.manager.session')->getNextSessions();
        $inscriptions         = $this->get('hopitalnumerique_module.manager.inscription')->findAll();
        foreach( $inscriptions as $inscription){
            if( $inscription->getEtatInscription()->getId() == 406)
                $blocSessions['inscriptions']++;

            if( $inscription->getEtatParticipation() && $inscription->getEtatParticipation()->getId() == 411 && $inscription->getUser()->hasRoleAmbassadeur() && $inscription->getSession()->getModule()->getId() == 6 )
                $blocUser['ambassadeursMAPF']++;
        }

        //Bloc Paiements
        $factures = $this->get('hopitalnumerique_paiement.manager.facture')->findAll();
        $firstJanuary = new \DateTime( '01-01-' . date('Y') );
        foreach($factures as $facture){
            if( $facture->isPayee() && $facture->getDatePaiement() >= $firstJanuary )
                $blocPaiements['janvier'] += $facture->getTotal();
            
            if( !$facture->isPayee() )
                $blocPaiements['apayer'] += $facture->getTotal();
        }

        //get interventions + formations => montant en attente de paiement
        $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getForFactures();
        $formations    = $this->get('hopitalnumerique_module.manager.inscription')->getForFactures();
        $datas         = $this->get('hopitalnumerique_paiement.manager.remboursement')->calculPrice( $interventions, $formations );
        foreach($datas as $data)
            $blocPaiements['attente'] += $data->total;

        //Contributions Forum Experts
        $date = new \DateTime();
        $date->modify('-7 day');

        $boards = $this->get('ccdn_forum_forum.model.board')->findAllBoards();
        foreach($boards as $board ){
            $topics = $board->getTopics();
            foreach($topics as $topic){
                $posts = $topic->getPosts();
                foreach($posts as $post){
                    $user = $post->getCreatedBy();

                    if( $post->getCreatedDate() >= $date && $user->hasRoleExpert())
                        $blocUser['contribution']++;
                }
            }
        }

        return $this->render('HopitalNumeriqueAdminBundle:Default:index.html.twig', array(
            'userConf'          => $userConf,
            'blocUser'          => $blocUser,
            'blocObjets'        => $blocObjets,
            'blocForum'         => $blocForum,
            'blocInterventions' => $blocInterventions,
            'blocSessions'      => $blocSessions,
            'blocPaiements'     => $blocPaiements
        ));
    }

    /**
     * Enregistre l'ordre des blocks du dashboard admin de l'utilisateur
     *
     * @param  Request $request La requete
     *
     * @return json
     */
    public function reorderAction(Request $request)
    {
        $datas = $request->request->get('datas');
        $dashboardBack = array();
        foreach($datas as $one)
            $dashboardBack[ $one['id'] ] = array( 'row' => $one['row'], 'col' => $one['col'] );
        
        //On récupère l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();
        $user->setDashboardBack( json_encode($dashboardBack) );
        $this->getDoctrine()->getManager()->flush();

        return new Response('{"success":true}', 200);
    }





    /**
     * Retourne les données des blocks forum
     *
     * @return array
     */
    private function getBlockForum()
    {
        $since1Month = new \DateTime();
        $since1Month->modify(' - 1 month');

        $blocForum = array();
        $forums    = $this->get('ccdn_forum_forum.model.forum')->findAllForums();
        foreach($forums as $forum){
            $tool       = new Chaine( $forum->getName() );
            $forumDatas = array( 'titre' => 'Forum ' . $forum->getName(), 'topics' => 0, 'contributions' => 0, 'topics-sans-reponses' => 0  );

            $categories = $forum->getCategories();
            foreach ($categories as $categorie) {
                $boards = $categorie->getBoards();
                foreach ($boards as $board) {
                    $topics = $board->getTopics();
                    foreach ($topics as $topic) {
                        if( $topic->getCachedReplyCount() == 0)
                            $forumDatas['topics-sans-reponses']++;

                        $post = $topic->getLastPost();
                        if( $post->getCreatedDate()->modify('+ 1 month') >= new \DateTime() )
                            $forumDatas['topics']++;

                        if( $post->getCreatedDate() >= $since1Month )
                            $forumDatas['contributions']++;
                    }
                }
            }

            $blocForum[ $tool->minifie() ] = $forumDatas;
        }

        return $blocForum;
    }

    /**
     * Retourne les informations sur les blocs objets ( points dur, productions, top / bottom)
     *
     * @return array
     */
    private function getBlockObjets()
    {
        $blocObjets = array( 
            'points-durs'               => 0,
            'productions'               => 0,
            'publications-non-publiees' => 0,
            'top5-points-dur'           => array(),
            'bottom5-points-dur'        => array(),
            'top5-productions'          => array(),
            'bottom5-productions'       => array()
        );

        //Bloc "Publication" + TOP + BOTTOM
        $datas        = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsForDashboard();
        $publications = array();
        foreach( $datas as $one){
            if( !isset($publications[ $one['id'] ]))
                $publications[ $one['id'] ] = $one;

            $publications[ $one['id'] ]['types'][] = $one['typeId'];
            if( !is_null($one['parentId']) )
                $publications[ $one['id'] ]['types'][] = $one['parentId'];
        }

        $interval = new \DateInterval('P1M');
        $today    = new \DateTime('now');
        foreach($publications as $publication) {
            if( $publication['etat'] == 4 )
                $blocObjets['publications-non-publiees']++;

            //Points Durs
            if( in_array('184', $publication['types']) ){
                $blocObjets['points-durs']++;

                //Build Top 5
                $blocObjets['top5-points-dur'][] = $publication;

                //Bottom 5
                if( $publication['dateCreation']->add( $interval ) <= $today )
                    $blocObjets['bottom5-points-dur'][] = $publication;
            //Productions
            }else if( in_array('175', $publication['types']) ){
                $blocObjets['productions']++;

                //Build Top 5
                $blocObjets['top5-productions'][] = $publication;

                //Bottom 5
                if( $publication['dateCreation']->add( $interval ) <= $today )
                    $blocObjets['bottom5-productions'][] = $publication;
            }
        }

        $blocObjets['top5-points-dur']     = $this->get5('top', $blocObjets['top5-points-dur'] );
        $blocObjets['bottom5-points-dur']  = $this->get5('bottom', $blocObjets['bottom5-points-dur'] );
        $blocObjets['top5-productions']    = $this->get5('top', $blocObjets['top5-productions'] );
        $blocObjets['bottom5-productions'] = $this->get5('bottom', $blocObjets['bottom5-productions'] );
        
        return $blocObjets;
    }

    /**
     * Retourne les informations sur le block Utilisateurs ( Users + Ambassadeur + Experts )
     *
     * @return array
     */
    private function getBlockuser()
    {
        $blocUser = array( 
            'nb'                 => 0, 
            'actif'              => 0, 
            'es'                 => 0, 
            'ambCandidats'       => 0, 
            'ambassadeurs'       => 0, 
            'ambassadeursMAPF'   => 0, 
            'ambCandidatsRecues' => 0, 
            'conventions'        => 0, 
            'expCandidats'       => 0, 
            'experts'            => 0, 
            'expCandidatsRecues' => 0, 
            'contribution'       => 0
        );

        $users          = $this->get('hopitalnumerique_user.manager.user')->findAll();
        $blocUser['nb'] = count($users);

        //Get Questionnaire Infos
        $idExpert      = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('expert');
        $idAmbassadeur = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur');
        
        //Récupération des questionnaires et users
        $questionnaireByUser = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponseExiste($idExpert, $idAmbassadeur);
        
        //On récupère les candidatures refusées
        $refusCandidature = $this->get('hopitalnumerique_user.manager.refus_candidature')->getRefusCandidatureByQuestionnaire();

        foreach ($users as $user) {
            if( $user->getNbVisites() > 0 )
                $blocUser['actif']++;

            if( $user->hasRoleDirecteur() || $user->hasRoleEs() )
                $blocUser['es']++;
            elseif( $user->hasRoleAmbassadeur() )
                $blocUser['ambassadeurs']++;
            elseif( $user->hasRoleExpert() )
                $blocUser['experts']++;

            //Récupération des questionnaires rempli par l'utilisateur courant
            $questionnairesByUser = array_key_exists($user->getId(), $questionnaireByUser) ? $questionnaireByUser[ $user->getId() ] : array();
            
            //Récupèration d'un booléen : Vérification de réponses pour le questionnaire expert, que son role n'est pas expert et que sa candidature n'a pas encore été refusé
            if (in_array($idExpert, $questionnairesByUser) && !$user->hasRoleExpert() && !$this->get('hopitalnumerique_user.manager.refus_candidature')->refusExisteByUserByQuestionnaire($user->getId(), $idExpert, $refusCandidature))
                $blocUser['expCandidats']++;
            
            //Récupèration d'un booléen : Vérification de réponses pour le questionnaire expert, que son role n'est pas expert et que sa candidature n'a pas encore été refusé
            if (in_array($idAmbassadeur, $questionnairesByUser) && !$user->hasRoleAmbassadeur() && !$this->get('hopitalnumerique_user.manager.refus_candidature')->refusExisteByUserByQuestionnaire($user->getId(), $idAmbassadeur, $refusCandidature))
                $blocUser['ambCandidats']++;

            if (in_array($idExpert, $questionnairesByUser) && !$this->get('hopitalnumerique_user.manager.refus_candidature')->refusExisteByUserByQuestionnaire($user->getId(), $idExpert, $refusCandidature))
                $blocUser['expCandidatsRecues']++;

            if (in_array($idAmbassadeur, $questionnairesByUser) && !$this->get('hopitalnumerique_user.manager.refus_candidature')->refusExisteByUserByQuestionnaire($user->getId(), $idAmbassadeur, $refusCandidature))
                $blocUser['ambCandidatsRecues']++;
        }

        //get contractualisation stuff
        $blocUser['conventions'] = $this->get('hopitalnumerique_user.manager.contractualisation')->getContractualisationsARenouveler();

        return $blocUser;
    }

    /**
     * Tri le tableau en TOP / FLOP 5
     *
     * @param string $type  Top / bottom
     * @param array  $datas Tableau de données
     *
     * @return array
     */
    private function get5($type, $datas)
    {
        if( count($datas) == 0 )
            return $datas;

        $sort = array();
        foreach($datas as $k=>$v) {
            $sort['nbVue'][$k] = $v['nbVue'];
            $sort['titre'][$k] = $v['titre'];
        }

        //sort For Top
        if( $type == 'top')
            array_multisort($sort['nbVue'], SORT_DESC, $sort['titre'], SORT_ASC,$datas);
        else
            array_multisort($sort['nbVue'], SORT_ASC, $sort['titre'], SORT_ASC,$datas);

        return array_slice($datas, 0, 5);
    }

    /**
     * Construit le tableau du dashboard user
     *
     * @param array $dashboardBack Tableau de la config dashboard
     *
     * @return array
     */
    private function buildDashboardRows( $dashboardBack )
    {
        $datas = array();
        $datas[ 'users' ]               = array( 'row' => 1, 'col' => 1);
        $datas[ 'ambassadeurs' ]        = array( 'row' => 1, 'col' => 2);
        $datas[ 'experts' ]             = array( 'row' => 1, 'col' => 3);
        $datas[ 'publications' ]        = array( 'row' => 2, 'col' => 1);
        $datas[ 'top5-points-dur' ]     = array( 'row' => 2, 'col' => 2);
        $datas[ 'bottom5-points-dur' ]  = array( 'row' => 2, 'col' => 3);
        $datas[ 'top5-productions' ]    = array( 'row' => 3, 'col' => 1);
        $datas[ 'bottom5-productions' ] = array( 'row' => 3, 'col' => 2);
        $datas[ 'interventions' ]       = array( 'row' => 3, 'col' => 3);
        $datas[ 'inscriptions' ]        = array( 'row' => 4, 'col' => 1);
        $datas[ 'sessions' ]            = array( 'row' => 4, 'col' => 2);
        $datas[ 'paiements' ]           = array( 'row' => 4, 'col' => 3);

        //Forum blocs
        $forums = $this->get('ccdn_forum_forum.model.forum')->findAllForums();
        $row    = 5;
        $col    = 1;
        foreach($forums as $forum){
            $tool = new Chaine( $forum->getName() );
            $datas[ $tool->minifie() ] = array( 'row' => $row, 'col' => $col);

            $col++;
            if( $col == 4){
                $row++;
                $col = 1;
            }
        }

        if( !is_null($dashboardBack) )
            $datas = array_replace($datas, $dashboardBack);
        
        return $datas;
    }
}