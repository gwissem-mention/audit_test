<?php

namespace HopitalNumerique\ModuleBundle\Manager;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ModuleBundle\Entity\Inscription;
use HopitalNumerique\PaiementBundle\Entity\Facture;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

/**
 * Manager de l'entité Inscription.
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ModuleBundle\Entity\Inscription';

    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager UserManager
     */
    private $_userManager;

    /**
     * Constructeur du manager de Session.
     *
     * @param \Doctrine\ORM\EntityManager                      $em          EntityManager
     * @param \HopitalNumerique\UserBundle\Manager\UserManager $userManager UserManager
     * @param Container                                        $container
     */
    public function __construct(EntityManager $em, UserManager $userManager, Container $container)
    {
        parent::__construct($em);

        $this->_userManager = $userManager;
        $this->container = $container;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau.
     *
     * @param \StdClass $condition
     *
     * @return array
     */
    public function getDatasForGrid(\StdClass $condition = null)
    {
        $inscriptions = [];

        $results = $this->getRepository()->getDatasForGrid($condition)->getQuery()->getResult();

        foreach ($results as $key => $result) {
            $inscriptions[$result['id']] = $result;

            // ----Traitement pour transformer le prénom "Jean-luc robert" en "Jean-Luc Robert"
            //Récupération du prénom
            $prenom = strtolower($result['userPrenom']);
            //Découpage du prénom sur le tiret
            $tempsPrenom = explode('-', $prenom);
            //Unsset de la variable
            $prenom = '';
            //Pour chaque bout on met une MAJ sur la première lettre de chaque mot, si il y en plusieurs c'est qu'il y avait un -
            foreach ($tempsPrenom as $key => $tempPrenom) {
                $prenom .= ('' !== $prenom) ? ('-' . ucwords($tempPrenom)) : ucwords($tempPrenom);
            }

            // ----Mise en majuscule du nom
            $nom = strtoupper($result['userNom']);

            //Suppression du nom et prenom
            unset($inscriptions[$result['id']]['userNom']);
            unset($inscriptions[$result['id']]['userPrenom']);

            //Ajout de la colonne "Prenom NOM"
            $inscriptions[$result['id']]['nomPrenom'] = $prenom . ' ' . $nom;
        }

        return array_values($inscriptions);
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
        $domainesIds = $this->_userManager->getUserConnected()->getDomainesId();
        $inscriptions = $this->getRepository()->getAllDatasForGrid($domainesIds, $condition)->getQuery()->getResult();

        $result = [];

        /**
         * @var             $key
         * @var Inscription $inscription
         */
        foreach ($inscriptions as $key => $inscription) {
            $nomPrenom = $inscription->getUser()->getAppellation();

            $nbInscritsAccepte = 0;
            $nbInscritsEnAttente = 0;
            $nbPlacesRestantes = $inscription->getSession()->getNombrePlaceDisponible();

            /** @var Inscription $inscriptionDeLaSession */
            foreach ($inscription->getSession()->getInscriptions() as $inscriptionDeLaSession) {
                if ($inscriptionDeLaSession->getEtatInscription()->getId() === 406) {
                    ++$nbInscritsEnAttente;
                } elseif ($inscriptionDeLaSession->getEtatInscription()->getId() === 407) {
                    ++$nbInscritsAccepte;
                    --$nbPlacesRestantes;
                }
            }

            $domaineNom = '';
            foreach ($inscription->getSession()->getModule()->getDomaines() as $domaine) {
                if ($domaineNom !== '') {
                    $domaineNom .= ' ; ';
                }
                $domaineNom .= $domaine->getNom();
            }

            $result[$key] = [
                'id' => $inscription->getId(),
                'userId' => $inscription->getUser()->getId(),
                'sessionId' => $inscription->getSession()->getId(),
                'moduleTitre' => $inscription->getSession()->getModule()->getTitre(),
                'dateSession' => $inscription->getSession()->getDateSession(),
                'nomPrenom' => $nomPrenom,
                'userRegion' => (!is_null($inscription->getUser()->getRegion()))
                    ? $inscription->getUser()->getRegion()->getLibelle()
                    : '',
                'userProfil' => (!is_null($inscription->getUser()->getProfileType()))
                    ? $inscription->getUser()->getProfileType()->getLibelle() : '',
                'roles' => $inscription->getUser()->getRoles(),
                'commentaire' => $inscription->getCommentaire(),
                'etatInscription' => $inscription->getEtatInscription()->getLibelle(),
                'nbInscrits' => $nbInscritsAccepte,
                'nbInscritsEnAttente' => $nbInscritsEnAttente,
                'placeRestantes' => $nbPlacesRestantes . '/' . $inscription->getSession()
                    ->getNombrePlaceDisponible(),
                'domaineNom' => $domaineNom,
            ];
        }

        return $result;
    }

    /**
     * Modifie l'état de toutes les inscriptions.
     *
     * @param array     $inscriptions Liste des inscriptions
     * @param Reference $ref          RefStatut à mettre
     */
    public function toogleEtatInscription($inscriptions, $ref)
    {
        foreach ($inscriptions as $inscription) {
            $inscription->setEtatInscription($ref);
            $this->em->persist($inscription);
        }

        $this->em->flush();
    }

    /**
     * Modifie l'état de toutes les participations.
     *
     * @param array     $inscriptions Liste des inscriptions
     * @param Reference $ref          RefStatut à mettre
     */
    public function toogleEtatParticipation($inscriptions, $ref)
    {
        foreach ($inscriptions as $inscription) {
            $inscription->setEtatParticipation($ref);
            $this->em->persist($inscription);
        }

        $this->em->flush();
    }

    /**
     * Modifie l'état de toutes les évaluations.
     *
     * @param array     $inscriptions Liste des inscriptions
     * @param Reference $ref          RefStatut à mettre
     */
    public function toogleEtatEvaluation($inscriptions, $ref)
    {
        foreach ($inscriptions as $inscription) {
            $inscription->setEtatEvaluation($ref);
            $this->em->persist($inscription);
        }

        $this->em->flush();
    }

    /**
     * Retourne la liste des inscriptions de l'utilisateur pour la création des factures.
     *
     * @param User $user L'utilisateur concerné
     *
     * @return array
     */
    public function getForFactures($user = null)
    {
        return $this->getRepository()->getForFactures($user)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des inscriptions pour une facture ordonnée par date de session.
     *
     * @param Facture $facture Identifiant de la facture
     *
     * @return array
     */
    public function getInscriptionsForFactureOrdered($facture = null)
    {
        return $this->getRepository()->getInscriptionsForFactureOrdered($facture)->getQuery()->getResult();
    }

    /**
     * Retourne un boolean pour dire si les inscriptions sont ok.
     *
     * @param User $user L'utilisateur concerné
     *
     * @return bool
     */
    public function allInscriptionsIsOk($user)
    {
        //Requete
        $inscriptions = $this->findBy(['user' => $user, 'etatParticipation' => 411]);

        //Parcours des résultats
        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            //Il faut que TOUTES les inscriptions de l'utilisateur soient "A participé" et "Évaluée"
            if ($inscription->getEtatParticipation()->getId() !== 411
                || $inscription->getEtatEvaluation()->getId() !== 29
            ) {
                //Inscriptions non conforme
                return false;
            }
        }

        return true;
    }

    /**
     * Retourne la liste des inscriptions de l'utilisateur.
     *
     * @param User $user L'utilisateur concerné
     *
     * @return array
     */
    public function getInscriptionsForUser($user)
    {
        return $this->getRepository()->getInscriptionsForUser($user);
    }

    /**
     * Créer un tableau formaté pour l'export CSV.
     *
     * @param array $modules Liste des modules
     * @param array $users   Liste des utilisateurs
     * @param       $primaryKeys
     *
     * @return array
     */
    public function buildForExport($modules, $users, $primaryKeys)
    {
        $colonnes = [
            'lastname' => 'Nom',
            'firstname' => 'Prénom',
        ];

        foreach ($modules as $module) {
            $colonnes['module' . $module->getId()] = $module->getTitre();
        }

        $inscriptions = $this->getRepository()->getInscriptionsByUser($primaryKeys)->getQuery()->getResult();
        $donnees = [];
        foreach ($inscriptions as $inscription) {
            $donnees[$inscription['userId']][$inscription['moduleId']] = date_format($inscription['date'], 'd/m/Y');
        }

        $datas = [];
        foreach ($users as $user) {
            $row = [];

            $row['lastname'] = $user->getLastname();
            $row['firstname'] = $user->getFirstname();
            foreach ($modules as $module) {
                $row['module' . $module->getId()] = isset($donnees[$user->getId()][$module->getId()])
                    ? $donnees[$user->getId()][$module->getId()] : '';
            }

            $datas[] = $row;
        }

        return ['colonnes' => $colonnes, 'datas' => $datas];
    }
}
