<?php

namespace HopitalNumerique\AdminBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\Contractualisation;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nodevo\ToolsBundle\Tools\Chaine;

/**
 * DefaultController.
 */
class DefaultController extends Controller
{
    protected $domains;

    /**
     * Index Action.
     */
    public function indexAction(Request $request)
    {
        //On récupère l'user connecté
        $user = $this->getUser();

        /* Gestion des domaines */
        $userDomains = $this->getUser()->getDomaines()->toArray();

        $selectedDomainId = $request->query->get('domaine');

        if (null !== $selectedDomainId && 'all' !== $selectedDomainId) {
            $this->domains = array_filter($userDomains, function ($domain) use ($selectedDomainId) {
                return $domain->getId() === (int) $selectedDomainId;
            });
        } else {
            $this->domains = $userDomains;
        }

        //récupère la conf (l'ordre) des blocks du dashboard de l'user connecté
        $userConf = $this->buildDashboardRows(json_decode($user->getDashboardBack(), true));

        //Initialisation des blocs
        list($blocUser, $blocAmbassadeur, $blocExpert) = $this->getBlockuser();
        $blocObjets = $this->getBlockObjets();
        $blocForum = $this->getBlockForum();
        $blocInterventions = $this->getBlockIntervention();
        $blocSessions = $this->getBlockSession();
        $blocPaiements = $this->get('hn.admin.payment_grid_block')->getBlockDatas($this->domains);
        $blocCDP = $this->get('hn.admin.cdp_grid_block')->getBlockDatas($this->domains);

        return $this->render('HopitalNumeriqueAdminBundle:Default:index.html.twig', [
            'anneeEnCours' => date('Y'),
            'userConf' => $userConf,
            'blocUser' => $blocUser,
            'blocAmbassadeur' => $blocAmbassadeur,
            'blocExpert' => $blocExpert,
            'blocObjets' => $blocObjets,
            'blocForum' => $blocForum,
            'blocInterventions' => $blocInterventions,
            'blocSessions' => $blocSessions,
            'blocPaiements' => $blocPaiements,
            'blockCDP' => $blocCDP,
            'userDomains' => $userDomains,
            'selectedDomain' => $selectedDomainId,
        ]);
    }

    /**
     * Enregistre l'ordre des blocks du dashboard admin de l'utilisateur.
     *
     * @param Request $request La requete
     *
     * @return JsonResponse
     */
    public function reorderAction(Request $request)
    {
        $datas = $request->request->get('datas');
        $dashboardBack = [];
        foreach ($datas as $one) {
            $dashboardBack[$one['id']] = [
                'row' => $one['row'],
                'col' => $one['col'],
                'visible' => true,
            ];
        }

        //On récupère l'user connecté
        $user = $this->getUser();
        $user->setDashboardBack(json_encode($dashboardBack));
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * Retourne les données des blocks forum.
     *
     * @return array
     */
    private function getBlockForum()
    {
        $postRepository = $this->get('hopitalnumerique_forum.repository.post');
        $topicRepository = $this->get('hopitalnumerique_forum.repository.topic');

        $since1Month = new \DateTime();
        $since1Month->modify(' - 1 month');

        return [
            'topics' => $topicRepository->countActiveTopicsByDomains($this->domains, $since1Month),
            'contributions' => $postRepository->countPostsByDomains($this->domains, $since1Month),
            'topics-sans-reponses' => $topicRepository->countUnreplyedTopcisByDomains($this->domains),
        ];
    }

    /**
     * Retourne les informations sur les blocs objets ( points dur, productions, top / bottom).
     *
     * @return array
     */
    private function getBlockObjets()
    {
        $noteRepository = $this->get('hopitalnumerique_objet.repository.note');
        $commentRepository = $this->get('hopitalnumerique_objet.repository.commentaire');

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
        $datas = $this->get('hopitalnumerique_objet.repository.objet')->getObjetsForDashboard($this->domains);
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
        foreach ($publications as $publication) {
            if ($publication['etat'] == 4) {
                $blocObjets['publications-non-publiees']++;
            }

            //Points Durs
            if (in_array('184', $publication['types'])) {
                $blocObjets['points-durs']++;

                //Build Top 5
                $blocObjets['top5-points-dur'][] = $publication;

                //Bottom 5 - On affiche ceux qui ont plus d'un mois
                if ($publication['dateCreation']->add($interval) <= $today) {
                    $blocObjets['bottom5-points-dur'][] = $publication;
                }
                //Productions
            } elseif (in_array('175', $publication['types'])) {
                $blocObjets['productions']++;

                //Build Top 5
                $blocObjets['top5-productions'][] = $publication;

                //Bottom 5
                if ($publication['dateCreation']->add($interval) <= $today) {
                    $blocObjets['bottom5-productions'][] = $publication;
                }
            }
        }


        // Pourcentage de publications ayant comme note moyenne + de 3.5
        $blocObjets['nb-notes'] = $noteRepository->countByDomains($this->domains, 3.5);
        $blocObjets['nb-commentaires'] = $commentRepository->countByDomains($this->domains);
        $blocObjets['pourcent-note-publication'] = round($noteRepository->computeAverageByDomains($this->domains, 3.5));
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
        $userRepository = $this->get('hopitalnumerique_user.repository.user');
        $questionnaireManager = $this->get('hopitalnumerique_questionnaire.manager.questionnaire');
        $refusCandidatureManager = $this->get('hopitalnumerique_user.manager.refus_candidature');
        $inscriptionRepository = $this->get('hn.module.repository.inscription');
        $postRepository = $this->get('hopitalnumerique_forum.repository.post');
        $contractRepository = $this->get('hopitalnumerique_user.repository.contractualisation');

        $domainNumeric = !empty(array_filter($this->domains, function (Domaine $domain) {
            return $domain->getId() === Domaine::DOMAINE_HOPITAL_NUMERIQUE_ID;
        }));

        $expertContributionDate = new \DateTime();
        $expertContributionDate->modify('-7 day');

        $blocUser = [
            'nb' => $userRepository->countUsersByDomains($this->domains),
            'actif' => $userRepository->countActiveUsersByDomains(
                $this->domains,
                (new \DateTime())->modify('last year')
            ),
            'es' => $userRepository->countEsUsersByDomains($this->domains),
        ];

        $blocExpert = $domainNumeric
            ? [
                'expCandidats' => 0,
                'experts' => 0,
                'expCandidatsRecues' => 0,
                'contribution' => $postRepository->countExpertContributionByDomains($this->domains, $expertContributionDate),
            ]
            : null
        ;

        $blocAmbassadeur = $domainNumeric
            ? [
                'ambCandidats' => 0,
                'ambassadeurs' => 0,
                'ambassadeursMAPF' => $inscriptionRepository->countAmbassadorsTrainedInMAPFByDomains($this->domains),
                'ambCandidatsRecues' => 0,
                'conventions' => $contractRepository->countExpiredContractForAmbassadorByDomains($this->domains),
            ]
            : null
        ;

        if ($domainNumeric) {
            /** @var User[] $users */
            $users = $this->get('hopitalnumerique_user.manager.user')->findByDomaines(new ArrayCollection($this->domains));

            //Get Questionnaire Infos
            $idExpert = $questionnaireManager->getQuestionnaireId('expert');
            $idAmbassadeur = $questionnaireManager->getQuestionnaireId('ambassadeur');

            //Récupération des questionnaires et users
            $questionnaireByUser = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponseExiste();

            //On récupère les candidatures refusées
            $refusCandidature = $refusCandidatureManager->getRefusCandidatureByQuestionnaire();

            foreach ($users as $user) {
                // Ne pas prendre en compte les utilisateurs inactifs
                if ($user->getEtat()->getId() !== 3) {
                    continue;
                }

                if ($domainNumeric && $user->hasRoleAmbassadeur()) {
                    $blocAmbassadeur['ambassadeurs']++;
                } elseif ($domainNumeric && $user->hasRoleExpert()) {
                    $blocExpert['experts']++;
                }

                //Récupération des questionnaires rempli par l'utilisateur courant
                $questionnairesByUser = [];
                if (array_key_exists($user->getId(), $questionnaireByUser)) {
                    $questionnaireByUser = $questionnaireByUser[$user->getId()];
                }

                // Récupèration d'un booléen : Vérification de réponses pour le questionnaire expert,
                // que son role n'est pas expert et que sa candidature n'a pas encore été refusé
                if ($domainNumeric
                    && in_array($idExpert, $questionnairesByUser)
                    && !$user->hasRoleExpert()
                    && !$user->getAlreadyBeExpert()
                    && !$refusCandidatureManager->refusExisteByUserByQuestionnaire(
                        $user->getId(),
                        $idExpert,
                        $refusCandidature
                    )
                ) {
                    $blocExpert['expCandidats']++;
                }

                // Récupération d'un booléen : Vérification de réponses pour le questionnaire expert, que son role n'est
                // pas expert et que sa candidature n'a pas encore été refusé
                if ($domainNumeric
                    && in_array($idAmbassadeur, $questionnairesByUser)
                    && !$user->hasRoleAmbassadeur()
                    && !$user->getAlreadyBeAmbassadeur()
                    && !$refusCandidatureManager->refusExisteByUserByQuestionnaire(
                        $user->getId(),
                        $idAmbassadeur,
                        $refusCandidature
                    )
                ) {
                    $blocAmbassadeur['ambCandidats']++;
                }

                if ($domainNumeric
                    && in_array($idExpert, $questionnairesByUser)
                    && !$refusCandidatureManager->refusExisteByUserByQuestionnaire(
                        $user->getId(),
                        $idExpert,
                        $refusCandidature
                    )
                ) {
                    $blocExpert['expCandidatsRecues']++;
                }

                if ($domainNumeric
                    && in_array($idAmbassadeur, $questionnairesByUser)
                    && !$refusCandidatureManager->refusExisteByUserByQuestionnaire(
                        $user->getId(),
                        $idAmbassadeur,
                        $refusCandidature
                    )
                ) {
                    $blocAmbassadeur['ambCandidatsRecues']++;
                }
            }
        }

        return [$blocUser, $blocAmbassadeur, $blocExpert];
    }

    public function getBlockIntervention()
    {
        $canShow = !empty(array_filter($this->domains, function (Domaine $domain) {
            return $domain->getId() === Domaine::DOMAINE_HOPITAL_NUMERIQUE_ID;
        }));

        if (!$canShow) {
            return null;
        }

        $interventionRepository = $this->get('hopitalnumerique_intervention.repository.intervention_demande');
        $stats = $interventionRepository->getStats();

        return [
            'total' => $stats['total'],
            'demandees' => $stats['accepted'],
            'attente' => $stats['waiting'],
            'en-cours' => $stats['ambassadorsCount'],
            'refusees' => $stats['refused'],
            'annulees' => $stats['canceled'],
        ];
    }

    public function getBlockSession()
    {
        $inscriptionRepository = $this->container->get('hn.module.repository.inscription');
        $sessionRepository = $this->container->get('hn.module.repository.session');
        $year = intval(date('Y'));

        $blocSessions = [
            'next' => $sessionRepository->getNextSessionsByDomains($this->domains),
            'totalInscriptionsAnneeEnCours' => $inscriptionRepository->countInscriptionsByYear($year, $this->domains),
            'totalInscriptionsAnneePrecedente' => $inscriptionRepository->countInscriptionsByYear($year - 1, $this->domains),
            'totalParticipantsAnneeEnCours' => $inscriptionRepository->countUsersByYear($year, $this->domains),
            'totalParticipantsAnneePrecedente' => $inscriptionRepository->countUsersByYear($year - 1, $this->domains),
            'totalSessionsRisquees' => $this->container->get('hopitalnumerique_module.manager.session')->getSessionsRisqueesCount(),
        ];

        return $blocSessions;
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
        $visible = null === $dashboardBack;

        $datas = [];
        $datas['users'] = ['row' => 1, 'col' => 1, 'visible' => $visible];
        $datas['ambassadeurs'] = ['row' => 1, 'col' => 2, 'visible' => $visible];
        $datas['experts'] = ['row' => 1, 'col' => 3, 'visible' => $visible];
        $datas['publications'] = ['row' => 2, 'col' => 1, 'visible' => $visible];
        $datas['top5-points-dur'] = ['row' => 2, 'col' => 2, 'visible' => $visible];
        $datas['bottom5-points-dur'] = ['row' => 2, 'col' => 3, 'visible' => $visible];
        $datas['top5-productions'] = ['row' => 3, 'col' => 1, 'visible' => $visible];
        $datas['bottom5-productions'] = ['row' => 3, 'col' => 2, 'visible' => $visible];
        $datas['interventions'] = ['row' => 3, 'col' => 3, 'visible' => $visible];
        $datas['inscriptions'] = ['row' => 4, 'col' => 1, 'visible' => $visible];
        $datas['sessions'] = ['row' => 4, 'col' => 2, 'visible' => $visible];
        $datas['paiements'] = ['row' => 4, 'col' => 3, 'visible' => $visible];
        $datas['cdp'] = ['row' => 5, 'col' => 3, 'visible' => $visible];
        $datas['forum'] = ['row' => 6, 'col' => 1, 'visible' => $visible];

        if (!is_null($dashboardBack)) {
            $datas = array_replace_recursive($datas, $dashboardBack);
        }

        return $datas;
    }
}
