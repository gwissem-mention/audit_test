<?php

namespace HopitalNumerique\QuestionnaireBundle\Manager;

use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\EtablissementBundle\Manager\EtablissementManager;
use HopitalNumerique\QuestionnaireBundle\Entity\Question;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\QuestionnaireBundle\Entity\Occurrence;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Manager de l'entité Contractualisation.
 */
class QuestionnaireManager extends BaseManager
{
    protected $class = 'HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire';

    /**
     * @var OccurrenceManager OccurrenceManager
     */
    private $occurrenceManager;

    protected $questionnaireArray = [];
    protected $mailExpertReponses = [];
    protected $mailReponses = [];

    /**
     * @var ReponseManager $managerReponse
     */
    protected $managerReponse;

    /**
     * @var UserManager $userManager
     */
    protected $userManager;

    /**
     * @var EtablissementManager
     */
    protected $etablissementManager;

    /**
     * @var Router $router
     */
    private $router;

    /**
     * QuestionnaireManager constructor.
     *
     * @param EntityManager        $em
     * @param OccurrenceManager    $occurrenceManager
     * @param                      $managerReponse
     * @param UserManager          $userManager
     * @param EtablissementManager $etablissementManager
     * @param Router               $router
     * @param array                $options
     */
    public function __construct(
        EntityManager $em,
        OccurrenceManager $occurrenceManager,
        $managerReponse,
        UserManager $userManager,
        EtablissementManager $etablissementManager,
        Router $router,
        $options = []
    ) {
        parent::__construct($em);
        $this->questionnaireArray   = isset($options['idRoles']) ? $options['idRoles'] : [];
        $this->mailExpertReponses   = isset($options['mailExpertReponses']) ? $options['mailExpertReponses'] : [];
        $this->mailReponses         = isset($options['mailReponses']) ? $options['mailReponses'] : [];
        $this->occurrenceManager    = $occurrenceManager;
        $this->managerReponse       = $managerReponse;
        $this->userManager          = $userManager;
        $this->etablissementManager = $etablissementManager;
        $this->router               = $router;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau.
     *
     * @param \StdClass $condition
     *
     * @return array
     *
     * @author    Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid(\StdClass $condition = null)
    {
        $questionnairesForGrid = [];

        $domainesIds = $this->userManager->getUserConnected()->getDomainesId();

        $questionnaires = $this->getRepository()->getDatasForGrid($domainesIds, $condition)->getQuery()->getResult();

        foreach ($questionnaires as $questionnaire) {
            if (!array_key_exists($questionnaire['id'], $questionnairesForGrid)) {
                $questionnairesForGrid[$questionnaire['id']] = $questionnaire;
            } else {
                $questionnairesForGrid[$questionnaire['id']]['domaineNom'] .= ';' . $questionnaire['domaineNom'];
            }
        }

        return array_values($questionnairesForGrid);
    }

    /**
     * @param                 $idQuestionnaire
     * @param                 $idUser
     * @param Occurrence|null $occurrence
     * @param null            $paramId
     *
     * @return mixed
     */
    public function getQuestionsReponses($idQuestionnaire, $idUser, Occurrence $occurrence = null, $paramId = null)
    {
        return $this->getRepository()->getQuestionsReponses($idQuestionnaire, $idUser, $occurrence, $paramId);
    }

    /**
     * Get les utilisateurs qui ont répondu à ce questionnaire.
     *
     * @param int $idQuestionnaire
     *
     * @return mixed
     */
    public function getQuestionnaireRepondant($idQuestionnaire)
    {
        return $this->getRepository()->getQuestionnaireRepondant($idQuestionnaire)->getQuery()->getResult();
    }

    /**
     * Get les adresses mails dans le config.yml/parameter.yml de l'envoies des mails experts.
     *
     * @return array( 'adresse' => 'nom' )
     */
    public function getMailExpertReponses()
    {
        return $this->mailExpertReponses;
    }

    /**
     * Get les adresses mails dans le config.yml/parameter.yml de l'envoies des réponses.
     *
     * @return array( 'adresse' => 'nom' )
     */
    public function getMailReponses()
    {
        return $this->mailReponses;
    }

    /**
     * Id du questionnaire.
     *
     * @param $label
     *
     * @return int id du questionnaire s'il existe, sinon 0
     * @throws \Exception
     */
    public function getQuestionnaireId($label)
    {
        if (array_key_exists($label, $this->questionnaireArray)) {
            return $this->questionnaireArray[$label];
        } else {
            throw new \Exception(
                'Le label \''
                . $label
                . '\' ne correspond à aucun questionnaire dans le QuestionnaireManager. Liste des labels attentu : '
                . self::getLabelsQuestionnaire()
            );
        }
    }

    /**
     * Permet l'affichage des labels des questionnaires.
     *
     * @return string
     */
    public function getLabelsQuestionnaire()
    {
        //Variable de return
        $res = '';

        foreach ($this->questionnaireArray as $label => $id) {
            $res .= '\'' . $label . '\' ';
        }

        return $res;
    }

    /**
     * Renvoie une chaine de caractère correspondant aux données du formulaire soumis.
     *
     * @param array(HopitalNumerique\QuestionnaireBundle\Entity\Reponse) $reponses
     *
     * @return string Affichage du formulaire
     */
    public function getQuestionnaireFormateMail($reponses)
    {
        $candidature = '<ul>';

        foreach ($reponses as $key => $reponse) {
            switch ($reponse->getQuestion()->getTypeQuestion()->getLibelle()) {
                case 'entityradio':
                case 'entity':
                    $candidature .= '<li><strong>' . $reponse->getQuestion()->getLibelle() . '</strong> : ';
                    if (!is_null($reponse->getReference())) {
                        $candidature .= $reponse->getReference()->getLibelle();
                    }
                    $candidature .= '</li>';
                    break;
                case 'checkbox':
                    $candidature .= '<li><strong>' . $reponse->getQuestion()->getLibelle() . '</strong> : ' . ('1' == $reponse->getReponse() ? 'Oui' : 'Non') . '</li>';
                    break;
                case 'etablissement':
                    $candidature .= '<li><strong>' . $reponse->getQuestion()->getLibelle() . '</strong> : ';
                    if (!is_null($reponse->getEtablissement())) {
                        $candidature .= $reponse->getEtablissement()->getAppellation();
                    }
                    $candidature .= '</li>';
                    break;
                // Gestion très sale, à revoir au moment de la construction du
                // tableau de réponses avec des niveaux d'enfants/parents etc.
                case 'entitymultiple':
                case 'entitycheckbox':
                    //Affichage pour une possibilité de plusieurs réponses à cette question
                    $candidature .= '<li><strong>' . $reponse->getQuestion()->getLibelle() . '</strong> : <ul>';
                    foreach ($reponse->getReferenceMulitple() as $key => $referenceMultiple) {
                        $candidature .= '<li>';
                        $candidature .= $referenceMultiple->getLibelle();
                        $candidature .= '</li>';
                    }
                    $candidature .= '</ul></li>';
                    break;
                case 'etablissementmultiple':
                    //Affichage pour une possibilité de plusieurs réponses à cette question
                    $candidature .= '<li><strong>' . $reponse->getQuestion()->getLibelle() . '</strong> : <ul>';
                    foreach ($reponse->getEtablissementMulitple() as $key => $etablissementMultiple) {
                        $candidature .= '<li>';
                        $candidature .= $etablissementMultiple->getAppellation();
                        $candidature .= '</li>';
                    }
                    $candidature .= '</ul></li>';
                    break;
                default:
                    $candidature .= '<li><strong>' . $reponse->getQuestion()->getLibelle() . '</strong> : ' . $reponse->getReponse() . '</li>';
                    break;
            }
        }
        $candidature .= '</ul>';

        return $candidature;
    }

    /**
     * Créer un tableau formaté pour l'export CSV.
     *
     * @param int   $idQuestionnaire ID du questionnaire
     * @param array $users           Liste des utilisateurs
     *
     * @return array
     */
    public function buildForExport($idQuestionnaire, $users)
    {
        $questionnaire = $this->findOneBy(['id' => $idQuestionnaire]);

        //prepare colonnes
        $colonnes = [
            'id' => 'id_utilisateur',
            'occurrence' => 'Titre de l\'occurrence',
            'user' => 'Prénom et Nom de l\'utilisateur',
            'user_email' => 'Email de l\'utilisateur',
            'user_region' => 'Région de l\'utilisateur',
            'date_saisie' => 'Date de saisie',
        ];
        $emptyRow = ['id' => ''];
        $questions = $questionnaire->getQuestions();

        /** @var Question $question */
        foreach ($questions as $question) {
            $colonnes['question' . $question->getId()] = $question->getLibelle();
            $emptyRow['question' . $question->getId()] = '';
        }

        $datas = [];
        foreach ($users as $user) {
            $occurrenceReponses = [];
            if ($questionnaire->isOccurrenceMultiple()) {
                $occurences = $this->occurrenceManager->findBy(['questionnaire' => $questionnaire, 'user' => $user]);
                foreach ($occurences as $occurrence) {
                    $occurrenceReponses[] = $this->managerReponse->reponsesByQuestionnaireByUser(
                        $idQuestionnaire,
                        $user->getId(),
                        true,
                        $occurrence
                    );
                }
            } else {
                $occurrenceReponses[] = $this->managerReponse->reponsesByQuestionnaireByUser(
                    $idQuestionnaire,
                    $user->getId(),
                    true
                );
            }

            foreach ($occurrenceReponses as $reponses) {
                // prepare user infos
                // use this to clone the empty table $emptyRow => make sure we have at least an empty data
                $row = array_merge([], $emptyRow);
                $row['id'] = $user->getId();

                $reponsesIndexes = array_keys($reponses);
                $row['occurrence'] = (count($reponses) > 0 ? (null !== $reponses[$reponsesIndexes[0]]->getOccurrence()
                    ? $reponses[$reponsesIndexes[0]]->getOccurrence()->getLibelle() : '') : '');
                $row['user'] = $user->getPrenomNom();
                $row['user_email'] = $user->getEmail();
                $row['user_region'] = $user->getRegion() == null ? '' : $user->getRegion()->getLibelle();
                $row['date_saisie'] = count($reponses) > 0 ? (null !== $reponses[$reponsesIndexes[0]]->getDateCreation()
                    ? $reponses[$reponsesIndexes[0]]->getDateCreation()->format('d-m-Y H:i:s') : '') : '';

                /** @var Reponse $reponse */
                foreach ($reponses as $reponse) {
                    $question = $reponse->getQuestion();

                    $field = 'question' . $question->getId();

                    switch ($question->getTypeQuestion()->getLibelle()) {
                        case 'file':
                            $row[$field] = $this->router->generate('hopitalnumerique_reponse_download', [
                                'reponse' => $reponse->getId(),
                            ], Router::ABSOLUTE_URL);
                            break;
                        case 'entity':
                            $row[$field] = is_null($reponse->getReference()) ? '-'
                                : $reponse->getReference()->getLibelle();
                            break;
                        case 'entityradio':
                            $row[$field] = is_null($reponse->getReference()) ? '-'
                                : $reponse->getReference()->getLibelle();
                            break;
                        case 'entitymultiple':
                            //Si il y a des réponses à exporter on exporte les libellés des références concaténés
                            if (is_null($reponse->getReferenceMulitple())) {
                                $row[$field] = '-';
                            } else {
                                $lib = '';
                                $compteur = 0;
                                foreach ($reponse->getReferenceMulitple() as $reference) {
                                    ++$compteur;
                                    // Récupération du libellé de la référence
                                    // + ajout d'un tiret si on est pas à la fin
                                    $lib .= $reference->getLibelle()
                                        . ($compteur == count($reponse->getReferenceMulitple())
                                        ? '' : ' - ')
                                    ;
                                }
                                $row[$field] = $lib;
                            }
                            break;
                        case 'etablissementmultiple':
                            //Si il y a des réponses à exporter on exporte les libellés des références concaténés
                            if (is_null($reponse->getEtablissementMulitple())) {
                                $row[$field] = '-';
                            } else {
                                $lib = '';
                                $compteur = 0;
                                foreach ($reponse->getEtablissementMulitple() as $etablissement) {
                                    ++$compteur;
                                    $lib .= $etablissement->getNom()
                                            . ($compteur == count($reponse->getEtablissementMulitple())
                                            ? '' : ' - ')
                                    ;
                                }
                                $row[$field] = $lib;
                            }
                            break;
                        case 'entitycheckbox':
                            //Si il y a des réponses à exporter on exporte les libellés des références concaténés
                            if (is_null($reponse->getReferenceMulitple())) {
                                $row[$field] = '-';
                            } else {
                                $lib = '';
                                $compteur = 0;
                                foreach ($reponse->getReferenceMulitple() as $reference) {
                                    ++$compteur;
                                    // Récupération du libellé de la référence
                                    // + ajout d'un tiret si on est pas à la fin
                                    $lib .= $reference->getLibelle()
                                        . ($compteur == count($reponse->getReferenceMulitple())
                                        ? '' : ' - ')
                                    ;
                                }
                                $row[$field] = $lib;
                            }
                            break;
                        case 'checkbox':
                            $row[$field] = ('1' == $reponse->getReponse() ? 'Oui' : 'Non');
                            break;
                        default:
                            if ($reponse->getQuestion()->getAlias() == 'etablissement') {
                                $etablissementReponse = $this->etablissementManager->findOneBy(
                                    ['id' => $reponse->getReponse()]
                                );
                                $row[$field]          = ($etablissementReponse instanceof Etablissement)
                                    ? $etablissementReponse->getNom() : '';
                            } else {
                                $row[$field] = $reponse->getReponse();
                            }
                            break;
                    }
                }

                $datas[] = $row;
            }
        }

        return ['colonnes' => $colonnes, 'datas' => $datas];
    }

    /**
     * Retourne les questionnaires (avec leurs occurrences) d'un utilisateur.
     *
     * @param User $user Utilisateur
     *
     * @return array<\HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire> Questionnaires
     */
    public function findByUser(User $user)
    {
        return $this->getRepository()->findByUser($user);
    }

    /**
     * Retourne les questionnaires d'un domaine.
     *
     * @param Domaine $domaine Domaine
     *
     * @return \Doctrine\Common\Collections\Collection Questionnaires
     */
    public function findByDomaine(Domaine $domaine)
    {
        return $this->getRepository()->findByDomaine($domaine);
    }

    /**
     * Si le questionnaire a été répondu sans que le formulaire fut en occurrence multiple,
     * créé l'occurrence multiple pour ces réponses.
     *
     * @param Questionnaire $questionnaire
     */
    public function forceOccurrenceMultiple(Questionnaire $questionnaire)
    {
        $repondants = $this->userManager->getUsersByQuestionnaire($questionnaire->getId());

        foreach ($repondants as $repondant) {
            $occurrence = $this->occurrenceManager->findOneBy(
                ['questionnaire' => $questionnaire, 'user' => $repondant]
            );

            if (null === $occurrence) {
                $occurrence = $this->occurrenceManager->createEmpty();
                $occurrence->setUser($repondant);
                $occurrence->setQuestionnaire($questionnaire);
                $this->occurrenceManager->save($occurrence);
                $this->managerReponse->setOccurrenceByQuestionnaireAndUser($occurrence, $questionnaire, $repondant);
            }
        }
    }

    /**
     * Supprime les occurrences multiples d'un questionnaire
     * (ne conserve que la première créée pour conserver les réponses).
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire Questionnaire
     */
    public function deleteOccurrencesMultiples(Questionnaire $questionnaire)
    {
        $repondants = $this->userManager->getUsersByQuestionnaire($questionnaire->getId());

        foreach ($repondants as $repondant) {
            $this->occurrenceManager->deleteOccurrencesMultiplesByQuestionnaireAndUser($questionnaire, $repondant);
        }
    }

    /**
     * Retourne les questionnaires (avec leurs occurrences) d'un utilisateur
     * pour un domaine avec les dates de création et de dernières modifications.
     *
     * @param User    $user
     * @param Domaine $domaine
     * @param bool    $isLock  Filtre sur questionnaire.lock
     *
     * @return Questionnaire[]
     */
    public function findByUserAndDomaineWithDates(User $user, Domaine $domaine, $isLock = null)
    {
        $questionnaires = $this->getRepository()->findByUserAndDomaineWithDates($user, $domaine, $isLock);
        $occurrences = $this->occurrenceManager->findByUserWithDates($user);

        for ($i = 0; $i < count($questionnaires); ++$i) {
            $questionnaires[$i]['occurrences'] = [];

            foreach ($occurrences as $occurrence) {
                if ($questionnaires[$i][0]->getId() == $occurrence[0]->getQuestionnaire()->getId()) {
                    $questionnaires[$i]['occurrences'][] = $occurrence;
                }
            }
        }

        return $questionnaires;
    }
}
