<?php

namespace HopitalNumerique\AdminBundle\Controller;

use HopitalNumerique\UserBundle\Entity\Contractualisation;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nodevo\ToolsBundle\Tools\Chaine;

/**
 * DefaultController.
 */
class DefaultController extends Controller
{
    /**
     * Index Action.
     */
    public function indexAction(Request $request)
    {
        //On récupère l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();
        $currentDomaine = $this->get('hopitalnumerique_domaine.manager.domaine')->findOneById($request->getSession()->get('domaineId'));

        //récupère la conf (l'ordre) des blocks du dashboard de l'user connecté
        $userConf = $this->buildDashboardRows(json_decode($user->getDashboardBack(), true));
        $anneeEnCours = intval(date('Y'));

        //Initialisation des blocs
        $blocUser = $this->getBlockuser();
        $blocObjets = $this->getBlockObjets();
        $blocForum = $this->getBlockForum();
        $blocInterventions = ['total' => 0, 'demandees' => 0, 'attente' => 0, 'en-cours' => 0, 'refusees' => 0, 'annulees' => 0];
        $blocSessions = [
            'next' => [],
            'totalInscriptionsAnneeEnCours' => $this->container->get('hopitalnumerique_module.manager.inscription')->getCountForYear($anneeEnCours, $currentDomaine),
            'totalInscriptionsAnneePrecedente' => $this->container->get('hopitalnumerique_module.manager.inscription')->getCountForYear($anneeEnCours - 1, $currentDomaine),
            'totalParticipantsAnneeEnCours' => $this->container->get('hopitalnumerique_module.manager.inscription')->getUsersCountForYear($anneeEnCours, $currentDomaine),
            'totalParticipantsAnneePrecedente' => $this->container->get('hopitalnumerique_module.manager.inscription')->getUsersCountForYear($anneeEnCours - 1, $currentDomaine),
            'totalSessionsRisquees' => $this->container->get('hopitalnumerique_module.manager.session')->getSessionsRisqueesCount(),
        ];
        $blocPaiements = ['apayer' => 0, 'attente' => 0, 'janvier' => 0];

        //Bloc Interventions
        $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findAll();
        foreach ($interventions as $intervention) {
            $etat = $intervention->getInterventionEtat()->getId();

            ++$blocInterventions['total'];

            if ($etat == 14 || $etat == 17 || $etat == 18 || $etat == 19) {
                ++$blocInterventions['demandees'];
            } elseif ($etat == 15) {
                ++$blocInterventions['attente'];
            } elseif ($etat == 21) {
                ++$blocInterventions['en-cours'];
            } elseif ($etat == 16 || $etat == 20) {
                ++$blocInterventions['refusees'];
            } elseif ($etat == 309) {
                ++$blocInterventions['annulees'];
            }
        }

        //GME 01/09/15 : Ajout du filtre du domaine pour le compteur des inscriptions
        //Récupération des domaines de l'utilisateur courant
        $domainesUser = $user->getDomainesId();

        //Bloc Sessions
        $blocSessions['next'] = $this->get('hopitalnumerique_module.manager.session')->getNextSessions($domainesUser);
        $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findAll();

        foreach ($inscriptions as $inscription) {
            if ($inscription->getSession()->getModule()->getStatut()->getId() == 3) {
                if ($inscription->getSession()->getEtat()->getId() == 403) {
                    //GME 01/09/15 : Ajout du filtre du domaine pour le compteur des inscriptions
                    //Récupération des domaines de l'inscription
                    $domainesInscription = $inscription->getSession()->getModule()->getDomainesId();
                    $domaineInsriptionInDomaineUser = false;
                    foreach ($domainesInscription as $idDomaineInscription) {
                        if (in_array($idDomaineInscription, $domainesUser)) {
                            $domaineInsriptionInDomaineUser = true;
                            break;
                        }
                    }

                    if ($inscription->getEtatParticipation() && $inscription->getEtatParticipation()->getId() == 411
                        && $inscription->getUser()->hasRoleAmbassadeur() && $inscription->getSession()->getModule()->getId() == 6
                    ) {
                        ++$blocUser['ambassadeursMAPF'];
                    }
                }
            }
        }

        $blocPaiements = $this->get('hn.admin.payment_grid_block')->getBlockDatas();

        //Contributions Forum Experts
        $date = new \DateTime();
        $date->modify('-7 day');

        $boards = $this->get('ccdn_forum_forum.model.board')->findAllBoards();
        foreach ($boards as $board) {
            $topics = $board->getTopics();
            foreach ($topics as $topic) {
                $posts = $topic->getPosts();
                foreach ($posts as $post) {
                    $user = $post->getCreatedBy();

                    if ($post->getCreatedDate() >= $date && $user->hasRoleExpert()) {
                        ++$blocUser['contribution'];
                    }
                }
            }
        }

        return $this->render('HopitalNumeriqueAdminBundle:Default:index.html.twig', [
            'anneeEnCours' => $anneeEnCours,
            'userConf' => $userConf,
            'blocUser' => $blocUser,
            'blocObjets' => $blocObjets,
            'blocForum' => $blocForum,
            'blocInterventions' => $blocInterventions,
            'blocSessions' => $blocSessions,
            'blocPaiements' => $blocPaiements,
        ]);
    }

    /**
     * Enregistre l'ordre des blocks du dashboard admin de l'utilisateur.
     *
     * @param Request $request La requete
     *
     * @return json
     */
    public function reorderAction(Request $request)
    {
        $datas = $request->request->get('datas');
        $dashboardBack = [];
        foreach ($datas as $one) {
            $dashboardBack[$one['id']] = ['row' => $one['row'], 'col' => $one['col']];
        }

        //On récupère l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();
        $user->setDashboardBack(json_encode($dashboardBack));
        $this->getDoctrine()->getManager()->flush();

        return new Response('{"success":true}', 200);
    }

    /**
     * Retourne les données des blocks forum.
     *
     * @return array
     */
    private function getBlockForum()
    {
        $since1Month = new \DateTime();
        $since1Month->modify(' - 1 month');

        $blocForum = [];
        $forums = $this->get('ccdn_forum_forum.model.forum')->findAllForums();
        foreach ($forums as $forum) {
            $tool = new Chaine($forum->getName());
            $forumDatas = ['titre' => 'Forum ' . $forum->getName(), 'topics' => 0, 'contributions' => 0, 'topics-sans-reponses' => 0];

            $categories = $forum->getCategories();
            foreach ($categories as $categorie) {
                $boards = $categorie->getBoards();
                foreach ($boards as $board) {
                    $topics = $board->getTopics();
                    foreach ($topics as $topic) {
                        if ($topic->getCachedReplyCount() == 0) {
                            ++$forumDatas['topics-sans-reponses'];
                        }

                        $lastPost = $topic->getLastPost();
                        if (!is_null($lastPost) && $lastPost->getCreatedDate()->modify('+ 1 month') >= new \DateTime()) {
                            ++$forumDatas['topics'];
                        }

                        $posts = $topic->getPosts();
                        foreach ($posts as $post) {
                            if ($post->getCreatedDate() >= $since1Month) {
                                ++$forumDatas['contributions'];
                            }
                        }
                    }
                }
            }

            $blocForum['forum-' . $tool->minifie()] = $forumDatas;
        }

        return $blocForum;
    }

    /**
     * Retourne les informations sur les blocs objets ( points dur, productions, top / bottom).
     *
     * @return array
     */
    private function getBlockObjets()
    {
        $blocObjets = [
            'points-durs' => 0,
            'productions' => 0,
            'publications-non-publiees' => 0,
            'nb-notes' => 0,
            'nb-commentaires' => 0,
            'pourcent-note-publication' => 0,
            'top5-points-dur' => [],
            'bottom5-points-dur' => [],
            'top5-productions' => [],
            'bottom5-productions' => [],
        ];

        //Bloc "Publication" + TOP + BOTTOM
        $datas = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsForDashboard();
        $publications = [];
        foreach ($datas as $one) {
            if (!isset($publications[$one['id']])) {
                $publications[$one['id']] = $one;
            }

            $publications[$one['id']]['types'][] = $one['typeId'];
            if (!is_null($one['parentId'])) {
                $publications[$one['id']]['types'][] = $one['parentId'];
            }
        }

        $interval = new \DateInterval('P1M');
        $today = new \DateTime('now');
        $notes = $this->get('hopitalnumerique_objet.manager.note')->findNoteByDomaine(1);
        $commentaires = $this->get('hopitalnumerique_objet.manager.commentaire')->findCommentaireByDomaine(1);
        foreach ($publications as $publication) {
            if ($publication['etat'] == 4) {
                ++$blocObjets['publications-non-publiees'];
            }

            //Points Durs
            if (in_array('184', $publication['types'])) {
                ++$blocObjets['points-durs'];

                //Build Top 5
                $blocObjets['top5-points-dur'][] = $publication;

                //Bottom 5 - On affiche ceux qui ont plus d'un mois
                if ($publication['dateCreation']->add($interval) <= $today) {
                    $blocObjets['bottom5-points-dur'][] = $publication;
                }
                //Productions
            } elseif (in_array('175', $publication['types'])) {
                ++$blocObjets['productions'];

                //Build Top 5
                $blocObjets['top5-productions'][] = $publication;

                //Bottom 5
                if ($publication['dateCreation']->add($interval) <= $today) {
                    $blocObjets['bottom5-productions'][] = $publication;
                }
            }
        }
        $publicationNoted = [];
        $publicationNotedHighValue = [];
        foreach ($notes as $note) {
            if (!is_null($note->getObjet())) {
                $publicationNotedHighValue[$note->getObjet()->getId()][] = $note->getNote();

                if (!in_array($note->getObjet()->getId(), $publicationNoted)) {
                    $publicationNoted[] = $note->getObjet()->getId();
                }
            }
        }

        //Calcul de la note moyenne des publication
        foreach ($publicationNotedHighValue as $key => $arrayNote) {
            $note = 0;
            $nbNote = 0;
            foreach ($arrayNote as $value) {
                ++$nbNote;
                $note += $value;
            }

            $note = round(($note / $nbNote), 1);

            if ($note >= 3.5) {
                $publicationNotedHighValue[$key] = $note;
            } else {
                unset($publicationNotedHighValue[$key]);
            }
        }

        $pourcentage = count($publicationNoted) == 0 ? 0 : (count($publicationNotedHighValue) / count($publicationNoted));

        $blocObjets['nb-notes'] = count($notes);
        $blocObjets['nb-commentaires'] = count($commentaires);
        $blocObjets['pourcent-note-publication'] = round($pourcentage * 100, 0);
        $blocObjets['top5-points-dur'] = $this->get5('top', $blocObjets['top5-points-dur']);
        $blocObjets['bottom5-points-dur'] = $this->get5('bottom', $blocObjets['bottom5-points-dur']);
        $blocObjets['top5-productions'] = $this->get5('top', $blocObjets['top5-productions']);
        $blocObjets['bottom5-productions'] = $this->get5('bottom', $blocObjets['bottom5-productions']);

        return $blocObjets;
    }

    /**
     * Retourne les informations sur le block Utilisateurs ( Users + Ambassadeur + Experts ).
     *
     * @return array
     */
    private function getBlockuser()
    {
        $blocUser = [
            'nb' => 0,
            'actif' => 0,
            'es' => 0,
            'ambCandidats' => 0,
            'ambassadeurs' => 0,
            'ambassadeursMAPF' => 0,
            'ambCandidatsRecues' => 0,
            'conventions' => 0,
            'expCandidats' => 0,
            'experts' => 0,
            'expCandidatsRecues' => 0,
            'contribution' => 0,
        ];
        /** @var User[] $users */
        $users = $this->get('hopitalnumerique_user.manager.user')->findUsersByDomaine(1);
        $blocUser['nb'] = count($users);

        //Get Questionnaire Infos
        $idExpert = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('expert');
        $idAmbassadeur = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur');

        //Récupération des questionnaires et users
        $questionnaireByUser = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponseExiste($idExpert, $idAmbassadeur);

        //On récupère les candidatures refusées
        $refusCandidature = $this->get('hopitalnumerique_user.manager.refus_candidature')->getRefusCandidatureByQuestionnaire();

        //get contractualisation stuff
        $blocUser['conventions'] = $this->get('hopitalnumerique_user.manager.contractualisation')->getContractualisationsARenouvelerForAmbassador();

        foreach ($users as $user) {
            //Ne pas prendre en compte les utilisateurs inactifs
            if ($user->getEtat()->getId() !== 3) {
                continue;
            }

            // Si l'utilisateur s'est connecté il y a moins de 1 an
            if ($user->getLastLogin() !== null && $user->getLastLogin()->diff(new \DateTime())->y < 1) {
                ++$blocUser['actif'];
            }

            if ($user->hasRoleDirecteur() || $user->hasRoleEs()) {
                ++$blocUser['es'];
            } elseif ($user->hasRoleAmbassadeur()) {
                ++$blocUser['ambassadeurs'];
            } elseif ($user->hasRoleExpert()) {
                ++$blocUser['experts'];
            }

            //Récupération des questionnaires rempli par l'utilisateur courant
            $questionnairesByUser = array_key_exists($user->getId(), $questionnaireByUser) ? $questionnaireByUser[$user->getId()] : [];

            //Récupèration d'un booléen : Vérification de réponses pour le questionnaire expert, que son role n'est pas expert et que sa candidature n'a pas encore été refusé
            if (in_array($idExpert, $questionnairesByUser) && !$user->hasRoleExpert() && !$user->getAlreadyBeExpert() && !$this->get('hopitalnumerique_user.manager.refus_candidature')->refusExisteByUserByQuestionnaire($user->getId(), $idExpert, $refusCandidature)) {
                ++$blocUser['expCandidats'];
            }

            //Récupèration d'un booléen : Vérification de réponses pour le questionnaire expert, que son role n'est pas expert et que sa candidature n'a pas encore été refusé
            if (in_array($idAmbassadeur, $questionnairesByUser) && !$user->hasRoleAmbassadeur() && !$user->getAlreadyBeAmbassadeur() && !$this->get('hopitalnumerique_user.manager.refus_candidature')->refusExisteByUserByQuestionnaire($user->getId(), $idAmbassadeur, $refusCandidature)) {
                ++$blocUser['ambCandidats'];
            }

            if (in_array($idExpert, $questionnairesByUser) && !$this->get('hopitalnumerique_user.manager.refus_candidature')->refusExisteByUserByQuestionnaire($user->getId(), $idExpert, $refusCandidature)) {
                ++$blocUser['expCandidatsRecues'];
            }

            if (in_array($idAmbassadeur, $questionnairesByUser) && !$this->get('hopitalnumerique_user.manager.refus_candidature')->refusExisteByUserByQuestionnaire($user->getId(), $idAmbassadeur, $refusCandidature)) {
                ++$blocUser['ambCandidatsRecues'];
            }
        }

        return $blocUser;
    }

    /**
     * Tri le tableau en TOP / FLOP 5.
     *
     * @param string $type  Top / bottom
     * @param array  $datas Tableau de données
     *
     * @return array
     */
    private function get5($type, $datas)
    {
        if (count($datas) == 0) {
            return $datas;
        }

        $sort = [];
        foreach ($datas as $k => $v) {
            $sort['nbVue'][$k] = $v['nbVue'];
            $sort['titre'][$k] = $v['titre'];
        }

        //sort For Top
        if ($type == 'top') {
            array_multisort($sort['nbVue'], SORT_DESC, $sort['titre'], SORT_ASC, $datas);
        } else {
            array_multisort($sort['nbVue'], SORT_ASC, $sort['titre'], SORT_ASC, $datas);
        }

        return array_slice($datas, 0, 5);
    }

    /**
     * Construit le tableau du dashboard user.
     *
     * @param array $dashboardBack Tableau de la config dashboard
     *
     * @return array
     */
    private function buildDashboardRows($dashboardBack)
    {
        $datas = [];
        $datas['users'] = ['row' => 1, 'col' => 1];
        $datas['ambassadeurs'] = ['row' => 1, 'col' => 2];
        $datas['experts'] = ['row' => 1, 'col' => 3];
        $datas['publications'] = ['row' => 2, 'col' => 1];
        $datas['top5-points-dur'] = ['row' => 2, 'col' => 2];
        $datas['bottom5-points-dur'] = ['row' => 2, 'col' => 3];
        $datas['top5-productions'] = ['row' => 3, 'col' => 1];
        $datas['bottom5-productions'] = ['row' => 3, 'col' => 2];
        $datas['interventions'] = ['row' => 3, 'col' => 3];
        $datas['inscriptions'] = ['row' => 4, 'col' => 1];
        $datas['sessions'] = ['row' => 4, 'col' => 2];
        $datas['paiements'] = ['row' => 4, 'col' => 3];

        //Forum blocs
        $forums = $this->get('ccdn_forum_forum.model.forum')->findAllForums();
        $row = 5;
        $col = 1;
        foreach ($forums as $forum) {
            $tool = new Chaine($forum->getName());
            $datas['forum-' . $tool->minifie()] = ['row' => $row, 'col' => $col];

            ++$col;
            if ($col == 4) {
                ++$row;
                $col = 1;
            }
        }

        if (!is_null($dashboardBack)) {
            $datas = array_replace($datas, $dashboardBack);
        }

        return $datas;
    }
}
