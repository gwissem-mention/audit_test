<?php

namespace HopitalNumerique\ModuleBundle\Manager;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ModuleBundle\Entity\Inscription;
use HopitalNumerique\ModuleBundle\Entity\Session;
use HopitalNumerique\ModuleBundle\Entity\SessionStatus;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\QuestionnaireBundle\Manager\ReponseManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Manager de l'entité Session.
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class SessionManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ModuleBundle\Entity\Session';

    /**
     * @var ReponseManager ReponseManager
     */
    private $reponseManager;

    /**
     * @var ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * @var UserManager UserManager
     */
    private $userManager;

    /**
     * Constructeur du manager de Session.
     *
     * @param \Doctrine\ORM\EntityManager $em               EntityManager
     * @param ReponseManager              $reponseManager   ReponseManager
     * @param ReferenceManager            $referenceManager ReferenceManager
     * @param UserManager                 $userManager      UserManager
     */
    public function __construct(
        EntityManager $em,
        ReponseManager $reponseManager,
        ReferenceManager $referenceManager,
        UserManager $userManager
    ) {
        parent::__construct($em);

        $this->reponseManager = $reponseManager;
        $this->referenceManager = $referenceManager;
        $this->userManager = $userManager;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau.
     *
     * @param \StdClass|null $condition
     *
     * @return array
     */
    public function getDatasForGrid(\StdClass $condition = null)
    {
        $sessions = $this->getRepository()->getDatasForGrid($condition)->getQuery()->getResult();

        $result = [];

        /**
         * @var         $key
         * @var Session $session
         */
        foreach ($sessions as $key => $session) {
            $nbInscritsAccepte = 0;
            $nbInscritsEnAttente = 0;
            $nbPlacesRestantes = $session->getNombrePlaceDisponible();

            /** @var Inscription $inscription */
            foreach ($session->getInscriptions() as $inscription) {
                if (!is_null($inscription->getEtatInscription())) {
                    if ($inscription->getEtatInscription()->getId() === SessionStatus::STATUT_FORMATION_WAITING_ID) {
                        $nbInscritsEnAttente++;
                    } elseif ($inscription->getEtatInscription()->getId() === SessionStatus::STATUT_FORMATION_ACCEPTED_ID) {
                        ++$nbInscritsAccepte;
                        --$nbPlacesRestantes;
                    }
                }
            }

            $result[$key] = [
                'id' => $session->getId(),
                'dateOuvertureInscription' => $session->getDateOuvertureInscription(),
                'dateFermetureInscription' => $session->getDateFermetureInscription(),
                'dateSession' => $session->getDateSession(),
                'duree' => $session->getDuree()->getLibelle(),
                'horaires' => $session->getHoraires(),
                'nbInscrits' => $nbInscritsAccepte,
                'nbInscritsEnAttente' => $nbInscritsEnAttente,
                'placeRestantes' => $nbPlacesRestantes . '/' . $session->getNombrePlaceDisponible(),
                'etat' => $session->getEtat()->getLibelle(),
                'archiver' => $session->getArchiver(),
            ];
        }

        return $result;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau.
     *
     * @param null $condition
     *
     * @return array
     */
    public function getAllDatasForGrid($condition = null)
    {
        $domainesIds = $this->userManager->getUserConnected()->getDomainesId();

        $sessions = $this->getRepository()->getAllDatasForGrid($domainesIds, $condition)->getQuery()->getResult();

        $result = [];

        /**
         * @var         $key
         * @var Session $session
         */
        foreach ($sessions as $key => $session) {
            $nbInscritsAccepte = 0;
            $nbInscritsEnAttente = 0;
            $nbPlacesRestantes = $session->getNombrePlaceDisponible();

            /** @var Inscription $inscription */
            foreach ($session->getInscriptions() as $inscription) {
                if ($inscription->getEtatInscription()->getId() === SessionStatus::STATUT_FORMATION_WAITING_ID) {
                    $nbInscritsEnAttente++;
                } elseif ($inscription->getEtatInscription()->getId() === SessionStatus::STATUT_FORMATION_ACCEPTED_ID) {
                    ++$nbInscritsAccepte;
                    --$nbPlacesRestantes;
                }
            }

            $domaineNom = '';
            foreach ($session->getModule()->getDomaines() as $domaine) {
                if ($domaineNom !== '') {
                    $domaineNom .= ' ; ';
                }
                $domaineNom .= $domaine->getNom();
            }

            $result[$key] = [
                'id' => $session->getId(),
                'moduleTitre' => $session->getModule()->getTitre(),
                'dateOuvertureInscription' => $session->getDateOuvertureInscription(),
                'dateFermetureInscription' => $session->getDateFermetureInscription(),
                'dateSession' => $session->getDateSession(),
                'duree' => $session->getDuree()->getLibelle(),
                'horaires' => $session->getHoraires(),
                'nbInscrits' => $nbInscritsAccepte,
                'nbInscritsEnAttente' => $nbInscritsEnAttente,
                'placeRestantes' => $nbPlacesRestantes . '/' . $session->getNombrePlaceDisponible(),
                'etat' => $session->getEtat()->getLibelle(),
                'archiver' => $session->getArchiver(),
                'formateur' => $session->getFormateur()->getNomPrenom(),
                'domaineNom' => $domaineNom,
            ];
        }

        return $result;
    }

    /**
     * Retourne la liste des sessions du domaine courrant étant en court d'inscription et active.
     *
     * @param int $idDomaine Domaine concerné
     *
     * @return array
     */
    public function getSessionsInscriptionOuverteModuleDomaine($idDomaine)
    {
        return $this->getRepository()->getSessionsInscriptionOuverteModuleDomaine($idDomaine)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des sessions du formateur.
     *
     * @param User $user L'utilisateur concerné
     * @param Domaine $domain
     *
     * @return array
     */
    public function getSessionsForFormateur(User $user, Domaine $domain)
    {
        return $this->getRepository()->getSessionsForFormateur($user, $domain);
    }

    /**
     * Retourne la liste des sessions à évaluer pour le dashboard user.
     *
     * @param User $user L'utilisateur concerné
     * @param Domaine $domain
     *
     * @return array
     */
    public function getSessionsForDashboard(User $user, Domaine $domain)
    {
        return $this->getRepository()->getSessionsForDashboard($user, $domain)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des sessions ou l'user connecté est formateur.
     *
     * @param User $user L'utilisateur connecté
     * @param Domaine $domain
     *
     * @return array
     */
    public function getSessionsForFormateurForDashboard(User $user, Domaine $domain)
    {
        $before = $this->getRepository()->getSessionsForFormateur($user, $domain, 'beforeToday', 2);
        $after = $this->getRepository()->getSessionsForFormateur($user, $domain, 'afterToday', 2);

        return ['before' => $before, 'after' => $after];
    }

    /**
     * Retourne les évaluations pour l'export.
     *
     * @param int[]  $sessionIds Les IDs des sessions à exporter
     * @param string $charset    Encodage du CSV
     *
     * @return Response
     */
    public function getExportEvaluationsCsv(array $sessionIds, $charset)
    {
        $sessions = $this->findBy(['id' => $sessionIds]);

        $colonnes =
        [
            'Nom du module',
            'Date de session',
            'Participant - ID',
            'Participant - Nom',
        ];
        $datas = [];

        /** @var Session $session */
        foreach ($sessions as $session) {
            $inscriptions = $session->getInscriptionsAccepte();

            /** @var Inscription $inscription */
            foreach ($inscriptions as $inscription) {
                $hasReponses = false;
                $user = $inscription->getUser();
                $reponses = $this->reponseManager->reponsesByQuestionnaireByUser(
                    4,
                    $user->getId(),
                    true,
                    null,
                    $session->getId()
                );
                $row = [];

                /** @var Reponse $reponse */
                foreach ($reponses as $reponse) {
                    $question = $reponse->getQuestion();
                    $idQuestion = $question->getId();

                    //ajoute la question si non présente dans les colonnes
                    if (!isset($colonnes[$idQuestion])) {
                        $colonnes[$idQuestion] = $question->getLibelle();
                    }

                    //handle la réponse
                    switch ($question->getTypeQuestion()->getLibelle()) {
                        case 'checkbox':
                            $row[$idQuestion] = ('1' == $reponse->getReponse() ? 'Oui' : 'Non');
                            break;
                        case 'entityradio':
                            $question = $reponse->getQuestion();

                            $referenceReponse = $this->referenceManager->findOneBy(['id' => $reponse->getReponse()]);

                            if (!is_null($referenceReponse)) {
                                $row[$idQuestion] = $referenceReponse->getLibelle();
                            } else {
                                $row[$idQuestion] = 'Non renseigné';
                            }
                            break;
                        default:
                            $row[$idQuestion] = $reponse->getReponse();
                            break;
                    }

                    $hasReponses = true;
                }

                if (!$hasReponses) {
                    continue;
                }

                ksort($row);

                $tab = array_merge(
                    [
                        $session->getModule()->__toString(),
                        (null !== $session->getDateSession() ? $session->getDateSession()->format('d/m/y') : ''),
                        (null !== $inscription->getUser() ? $inscription->getUser()->getId() : ''),
                        (null !== $inscription->getUser() ? $inscription->getUser()->getAppellation() : ''),
                    ],
                    $row
                );
                $datas[] = $tab;
            }
        }

        if (empty($datas)) {
            $colonnes = [0 => 'Aucune donnée'];
            $datas[] = [0 => ''];
        }

        ksort($colonnes);

        return $this->exportCsv(
            array_values($colonnes),
            $datas,
            'export-session-evaluations.csv',
            $charset
        );
    }

    /**
     * Retourne les sessions à risque, càd n'ayant pas assez de participants pour des sessions prochaines.
     *
     * @return Session[]
     */
    public function getSessionsRisquees()
    {
        $dans3mois = new \DateTime();
        $dans3mois->add(new \DateInterval('P3M'));

        return $this->getRepository()->getSessionsRisquees(3, $dans3mois)->getQuery()->getResult();
    }

    /**
     * Retourne les sessions à risque, càd n'ayant pas assez de participants pour des sessions prochaines.
     *
     * @return int Total
     */
    public function getSessionsRisqueesCount()
    {
        return count($this->getSessionsRisquees());
    }
}
