<?php

namespace HopitalNumerique\AdminBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\NewAccountBundle\Domain\Command\ReorderDashboardCommand;
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
    /**
     * @var Domaine[]
     */
    protected $domains;

    /**
     * Index action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /**
         * Gestion des domaines
         * @var Domaine[] $userDomains
         */
        $userDomains = $this->getUser()->getDomaines()->toArray();

        $selectedDomainId = $request->query->get('domaine');

        //If selected domain id not supplied and current user has only one domain, select it by default.
        if (null === $selectedDomainId && 1 === count($userDomains)) {
            $selectedDomainId = $userDomains[0]->getId();
        }

        if ($selectedDomainId  === 'all') {
            $selectedDomainId = null;
        }

        if (null !== $selectedDomainId && 'all' !== $selectedDomainId) {
            $this->domains = array_filter($userDomains, function ($domain) use ($selectedDomainId) {
                return $domain->getId() === (int) $selectedDomainId;
            });
        } else {
            $this->domains = $userDomains;
        }

        //récupère la conf (l'ordre) des blocks du dashboard de l'user connecté
        $positions = $this
            ->get('dmishh.settings.settings_manager')
            ->get('account_dashboard_order', $this->get('security.token_storage')->getToken()->getUser())
        ;
        $positions = isset($positions['dashboard_back']) ? $positions['dashboard_back'] : null;

        $userConf = $this->buildDashboardRows($positions);

        //Initialisation des blocs
        list($blocUser, $blocAmbassadeur, $blocExpert) = $this->getBlockuser();
        $blocObjets = $this->getBlockObjets();
        $blocForum = $this->getBlockForum();
        $blocInterventions = $this->getBlockIntervention();
        $blocSessions = $this->getBlockSession();
        $blocPaiements = $this->get('hn.admin.payment_grid_block')->getBlockDatas($this->domains);
        $blocCDP = $this->get('hn.admin.cdp_grid_block')->getBlockDatas($this->domains);
        $blocCDPDiscussion = $this->get('hn.admin.cdp_grid_block')->getBlockDiscussionDatas($this->domains);

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
            'blockCDPDiscussion' => $blocCDPDiscussion,
            'userDomains' => $userDomains,
            'selectedDomain' => $selectedDomainId,
            'domainForFilters' => 1 === count($this->domains) ? current($this->domains)->getNom() : null,
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
        $command = new ReorderDashboardCommand('dashboard_back', $request->request->get('datas'), $this->getUser());

        $this->get('new_account.dashboard.command_handler.reorder')->handle($command);

        return new JsonResponse();
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
        $objectRepository = $this->get('hopitalnumerique_objet.repository.objet');

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
            'top5-productions-3mois' => [],
            'bottom5-productions-3mois' => [],
            'top5-points-dur-3mois' => [],
            'bottom5-points-dur-3mois' => [],
        ];

        //Bloc "Publication" + TOP + BOTTOM
        $datas = $objectRepository->getObjetsForDashboard($this->domains);
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

        foreach ($publications as $publication) {

            if ($publication['etat'] == 4) {
                $blocObjets['publications-non-publiees']++;
            }

            //Points Durs
            if (in_array('184', $publication['types'])) {
                $blocObjets['points-durs']++;

            //Productions
            } elseif (in_array('175', $publication['types'])) {
                $blocObjets['productions']++;
            }
        }

        // Pourcentage de publications ayant comme note moyenne + de 3.5
        $blocObjets['nb-notes'] = $noteRepository->countByDomains($this->domains, 3.5);
        $blocObjets['nb-commentaires'] = $commentRepository->countByDomains($this->domains);
        $blocObjets['pourcent-note-publication'] = round($noteRepository->computeAverageByDomains($this->domains, 3.5));

        $blocObjets['top5-points-dur'] = $objectRepository->getTopOrBottom($this->domains, 184, 'DESC');
        $blocObjets['bottom5-points-dur'] = $objectRepository->getTopOrBottom($this->domains, 184, 'ASC');
        $blocObjets['top5-productions'] = $objectRepository->getTopOrBottom($this->domains, 175, 'DESC');
        $blocObjets['bottom5-productions'] = $objectRepository->getTopOrBottom($this->domains, 175, 'ASC');
        $blocObjets['top5-productions-3mois'] = $objectRepository->getTopOrBottom($this->domains, 175, 'DESC', 3);
        $blocObjets['bottom5-productions-3mois'] = $objectRepository->getTopOrBottom($this->domains, 175, 'ASC', 3);
        $blocObjets['top5-points-dur-3mois'] = $objectRepository->getTopOrBottom($this->domains, 184, 'DESC', 3);
        $blocObjets['bottom5-points-dur-3mois'] = $objectRepository->getTopOrBottom($this->domains, 184, 'ASC', 3);

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
     * Construit le tableau du dashboard user.
     *
     * @param array $settings Tableau de la config dashboard
     *
     * @return array
     */
    private function buildDashboardRows($settings)
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
        $datas['cdp'] = ['row' => 5, 'col' => 3];
        $datas['forum'] = ['row' => 6, 'col' => 1];
        $datas['top5-productions-3mois'] = ['row' => 6, 'col' => 2];
        $datas['bottom5-productions-3mois'] = ['row' => 6, 'col' => 3];
        $datas['top5-points-dur-3mois'] = ['row' => 7, 'col' => 1];
        $datas['bottom5-points-dur-3mois'] = ['row' => 7, 'col' => 2];
        $datas['cdp_discussion'] = ['row' => 6, 'col' => 2];

        if (!is_null($settings)) {
            // Sort widgets
            uksort($datas, function ($a, $b) use ($settings) {
                if (!isset($settings[$a]['position']) || !isset($settings[$b]['position'])) {
                    return 1;
                }

                return $settings[$a]['position'] < $settings[$b]['position'] ? -1 : 1;
            });
        }

        // Set widget visibility and position
        $i = 0;
        $total = count($datas);
        foreach ($datas as $key => &$data) {
            $widgetSettings = (null !== $settings && isset($settings[$key])) ? $settings[$key] : null;

            if (null !== $widgetSettings) {
                $data['visible'] = isset($widgetSettings['visible']) ? $widgetSettings['visible'] : true;

                if (isset($widgetSettings['position'])) {
                    $position = $widgetSettings['position'];
                    $data['col'] = 1 + (($position - 1 ) % 3);
                    $data['row'] = (int) (1 + (floor(($position - 1 )/ 3)));
                }
            } else {
                $data['visible'] = false;
                $data['col'] = 1 + (($total - $i - 1) % 3);
                $data['row'] = (int) (1 + (floor(($total - $i) / 3)));
            }
            $i++;
        }

        return $datas;
    }
}
