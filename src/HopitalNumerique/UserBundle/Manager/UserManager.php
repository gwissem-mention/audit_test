<?php

namespace HopitalNumerique\UserBundle\Manager;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\PaiementBundle\Manager\RemboursementManager;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\Common\Collections\Collection;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\SecurityContext;

class UserManager extends BaseManager
{
    protected $class = '\HopitalNumerique\UserBundle\Entity\User';
    /**
     * @var SecurityContext
     */
    protected $securityContext;
    /**
     * @var \HopitalNumerique\QuestionnaireBundle\Manager\ReponseManager
     */
    protected $managerReponse;
    /**
     * @var RefusCandidatureManager
     */
    protected $managerRefusCandidature;

    /** @var DomaineManager */
    protected $managerDomaine;

    /** @var CurrentDomaine */
    protected $currentDomaine;

    protected $options;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    public function __construct(
        $managerUser,
        $securityContext,
        $managerReponse,
        $managerRefusCandidature,
        DomaineManager $managerDomaine,
        RemboursementManager $remboursementManager,
        CurrentDomaine $currentDomaine,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($managerUser);
        $this->securityContext = $securityContext;
        //Récupération des managers Réponses et Questionnaire
        $this->managerReponse = $managerReponse;
        $this->managerRefusCandidature = $managerRefusCandidature;
        $this->managerDomaine = $managerDomaine;
        $this->remboursementManager = $remboursementManager;
        $this->currentDomaine = $currentDomaine;
        $this->options = [];
        $this->eventDispatcher = $eventDispatcher;
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
        $users = $this->getRepository()->getDatasForGrid($condition)->getQuery()->getResult();
        $usersForGrid = [];

        $idExpert = 1;
        $idAmbassadeur = 2;

        //Récupération des questionnaires et users
        $questionnaireByUser = $this->managerReponse->reponseExiste();

        $aujourdHui = new \DateTime('now');
        $contractLimitDate = $aujourdHui->add(new \DateInterval('P45D'));

        $refusCandidature = $this->managerRefusCandidature->getRefusCandidatureByQuestionnaire();

        //Pour chaque utilisateur, set la contractualisation à jour
        foreach ($users as $user) {
            //Récupération des questionnaires rempli par l'utilisateur courant
            $questionnairesByUser =
                array_key_exists($user['id'], $questionnaireByUser) ? $questionnaireByUser[$user['id']] : [];

            //Récupèration d'un booléen : Vérification de réponses pour le questionnaire expert,
            //que son role n'est pas expert et que sa candidature n'a pas encore été refusé
            $user['expert'] = (in_array($idExpert, $questionnairesByUser)
                && !in_array('ROLE_EXPERT_6', $user['roles'])
                && !$this->managerRefusCandidature->refusExisteByUserByQuestionnaire(
                    $user['id'],
                    $idExpert,
                    $refusCandidature
                )
                && !$user['alreadyBeExpert']);

            //Récupèration d'un booléen : Vérification de réponses pour le questionnaire ambassadeur,
            //que son role n'est pas expert et que sa candidature n'a pas encore été refusé
            $user['ambassadeur'] = (in_array($idAmbassadeur, $questionnairesByUser)
                && !in_array('ROLE_AMBASSADEUR_7', $user['roles'])
                && !$this->managerRefusCandidature->refusExisteByUserByQuestionnaire(
                    $user['id'],
                    $idAmbassadeur,
                    $refusCandidature
                )
                && !$user['alreadyBeAmbassadeur']);

            $contractDate = new \DateTime($user['contra']);

            if (count(array_intersect($user['roles'], User::getRolesContractualisationUpToDate())) > 0) {
                $user['contra'] = $contractDate <= $contractLimitDate ? 'false' : 'true';
            } else {
                $user['contra'] = null;
            }

            unset($user['alreadyBeExpert']);
            unset($user['alreadyBeAmbassadeur']);
            $user['idUser'] = $user['id'];

            $usersForGrid[] = $user;
        }

        return $usersForGrid;
    }

    /**
     * Override : Récupère les données Etablissement pour le grid sous forme de tableau.
     *
     * @param \StdClass $condition
     *
     * @return array
     */
    public function getEtablissementForGrid(\StdClass $condition)
    {
        return $this->getRepository()->getEtablissementForGrid($condition)->getQuery()->getResult();
    }

    /**
     * Técupère les établissements pour l'export CSV.
     *
     * @return array
     */
    public function getEtablissementForExport($ids)
    {
        return $this->getRepository()->getEtablissementForExport($ids)->getQuery()->getResult();
    }

    /**
     * Modifie l'état de tous les users.
     *
     * @param array     $users Liste des utilisateurs
     * @param Reference $ref   RefStatut à mettre
     *
     * @return empty
     */
    public function toogleState($users, $ref)
    {
        foreach ($users as $user) {
            $user->setEtat($ref);
            $user->setEnabled(($ref->getId() == 3 ? 1 : 0));
            $this->em->persist($user);
        }

        //save
        $this->em->flush();
    }

    /**
     * On cherche a savoir si un user existe avec le role et la région de l'user modifié.
     *
     * @param User $user L'utilisateur modifié
     *
     * @return bool
     */
    public function userExistForRoleDirection($user)
    {
        return $this->getRepository()->userExistForRoleDirection($user)->getQuery()->getOneOrNullResult();
    }

    /**
     * Retourne la liste des ambassadeurs de la région et du domaine.
     *
     * @param Reference $region  La région filtrée
     * @param int       $domaine Le domaine fonctionnel
     *
     * @return array
     */
    public function getAmbassadeursByRegionAndDomaine($region, $domaine = null)
    {
        return $this->getRepository()->getAmbassadeursByRegionAndDomaine($region, $domaine)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des ambassadeurs de la région et de la publication.
     *
     * @param Reference $region La région filtrée
     * @param Objet     $objet  La publication
     *
     * @return array
     */
    public function getAmbassadeursByRegionAndProduction($region, $objet)
    {
        return $this->getRepository()->getAmbassadeursByRegionAndProduction($region, $objet)->getQuery()->getResult();
    }

    /**
     * [getUsersGroupeEtablissement description].
     *
     * @param array $criteres [description]
     *
     * @return [type]
     */
    public function getUsersGroupeEtablissement($criteres = [])
    {
        return $this->getRepository()->getUsersGroupeEtablissement($criteres)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des utilisateurs possédant le role demandé.
     *
     * @param string $role Le rôle demandé
     *
     * @return array
     */
    public function findUsersByRole($role)
    {
        return $this->getRepository()->findUsersByRole($role)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des utilisateurs possédant un des roles demandés.
     *
     * @param array $roles Rôles
     *
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Users
     */
    public function findUsersByRoles(array $roles)
    {
        return $this->getRepository()->findUsersByRoles($roles)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des utilisateurs étant assigné au domaine.
     *
     * @param int $idDomaine Identifiant du domaine à filtrer
     *
     * @return array
     */
    public function findUsersByDomaine($idDomaine)
    {
        return $this->getRepository()->findUsersByDomaine($idDomaine)->getQuery()->getResult();
    }

    /**
     * Retourne les utilisateurs liés à un de ces domaines.
     *
     * @param \Doctrine\Common\Collections\Collection $domaines Domaines
     *
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Utilisateurs
     */
    public function findByDomaines(Collection $domaines)
    {
        return $this->getRepository()->findByDomaines($domaines);
    }
    /**
     * Return users linked to network and defining one of specified domains (i.e. users referenced with at least one 'networkJob').
     *
     * @param \Doctrine\Common\Collections\Collection $domaines Domains
     *
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Utilisateurs
     */
    public function findNetworkUsersByDomaines(Collection $domains)
    {
        return $this->getRepository()->findNetworkUsersByDomaines($domains);
    }

    /**
     * Retourne le premier utilisateur correspondant au role et à la région demandés.
     *
     * @param string $role     Le rôle demandé
     * @param int    $idRegion Region demandée
     *
     * @return array
     */
    public function findUsersByRoleAndRegion($idregion, $role)
    {
        return $this->getRepository()->findUsersByRoleAndRegion($idregion, $role)->getQuery()->getOneOrNullResult();
    }

    /**
     * Retourne un unique CMSI.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return \HopitalNumerique\UserBundle\Entity\User|null Un CMSI si trouvé, sinon NIL
     */
    public function getCmsi(array $criteres = [])
    {
        return $this->getRepository()->getCmsi($criteres);
    }

    /**
     * Retourne un unique directeur.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return \HopitalNumerique\UserBundle\Entity\User|null Un directeur si trouvé, sinon NIL
     */
    public function getDirecteur(array $criteres)
    {
        return $this->getRepository()->getDirecteur($criteres);
    }

    /**
     * Retourne une liste d'ambassadeurs.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des ambassadeurs
     */
    public function getAmbassadeurs(array $criteres = [])
    {
        return $this->getRepository()->getAmbassadeurs($criteres);
    }

    /**
     * Retourne une liste des experts.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des experts
     */
    public function getExperts(array $criteres = [])
    {
        return $this->getRepository()->getExperts($criteres);
    }

    /**
     * Retourne une liste d'utilisateurs ES ou Enregistré.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des utilisateurs
     */
    public function getESAndEnregistres(array $criteres = [])
    {
        return $this->getRepository()->getESAndEnregistres($criteres);
    }

    /**
     * Retourne une liste d'utilisateurs Admin.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des utilisateurs
     */
    public function getAdmins(array $criteres = [])
    {
        return $this->getRepository()->getAdmins($criteres);
    }

    /**
     * Retourne une liste d'utilisateurs CMSI.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des utilisateurs
     */
    public function getCMSIs(array $criteres = [])
    {
        return $this->getRepository()->getCMSIs($criteres);
    }

    /**
     * Récupère les utilisateurs ayant répondues au questionnaire passé en paramètre.
     *
     * @param  int idQuestionnaire Identifiant du questionnaire
     *
     * @return result
     */
    public function getUsersByQuestionnaire($idQuestionnaire)
    {
        return $this->getRepository()->getUsersByQuestionnaire($idQuestionnaire)->getQuery()->getResult();
    }

    /**
     * @return \HopitalNumerique\UserBundle\Entity\User Utilisateur connecté
     */
    public function getUserConnected()
    {
        return $this->securityContext->getToken()->getUser();
    }

    /**
     * Récupère tous les utilisateurs (tous les rôles).
     *
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des utilisateurs
     */
    public function getAllUsers()
    {
        return $this->getRepository()->getAllUsers()->getQuery()->getResult();
    }

    /**
     * Récupère le nombre d'établissements connectés.
     *
     * @return int
     */
    public function getNbEtablissements(Domaine $domaine = null)
    {
        return $this->getRepository()->getNbEtablissements($domaine);
    }

    /**
     * Retourne la QueryBuilder avec les membres de la communauté de pratique.
     *
     * @param Groupe $groupe (optionnel) Groupe des membres
     *
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder
     */
    public function getCommunautePratiqueMembresQueryBuilder(Groupe $groupe = null, Domaine $domaine = null, $membreId = null)
    {
        return $this->getRepository()->getCommunautePratiqueMembresQueryBuilder($groupe, $domaine, $membreId);
    }

    /**
     * Retourne les membres de la communauté de pratique n'appartenant pas à tel groupe.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe Groupe
     *
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Utilisateurs
     */
    public function findCommunautePratiqueMembresNotInGroupe(Groupe $groupe)
    {
        return $this->getRepository()->findCommunautePratiqueMembresNotInGroupe($this->currentDomaine->get(), $groupe);
    }

    /**
     * Retourne des membres de la communauté de pratique au hasard.
     *
     * @param int         $nombreMembres
     * @param User[]|null $ignores
     *
     * @return User[]
     */
    public function findCommunautePratiqueRandomMembres($nombreMembres, array $ignores = null)
    {
        $domaine = $this->currentDomaine->get();

        return $this->getRepository()->findCommunautePratiqueRandomMembres($domaine, $nombreMembres, $ignores);
    }

    /**
     * Retourne de nombre de membres de la communauté de pratique.
     *
     * @return int Total
     */
    public function findCommunautePratiqueMembresCount()
    {
        return $this->getRepository()->findCommunautePratiqueMembresCount($this->currentDomaine->get());
    }

    /**
     * Désinscrit un utilisateur de la communauté de partique.
     *
     * @param \HopitalNumerique\UserBundle\Manager\User $user Membre à désinscrire
     */
    public function desinscritCommunautePratique(User $user)
    {
        // On supprime les liens entre le membre et les groupes
        if ($user->getCommunautePratiqueGroupes() !== null) {
            foreach ($user->getCommunautePratiqueGroupes() as $groupe) {
                $user->removeCommunautePratiqueGroupe($groupe);
            }
        }
        if ($user->getCommunautePratiqueAnimateurGroupes() !== null) {
            foreach ($user->getCommunautePratiqueAnimateurGroupes() as $groupe) {
                $user->removeCommunautePratiqueAnimateurGroupe($groupe);
            }
        }

        $this->save($user);
    }

    /**
     * Retourne le référent d'une région.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $region Région
     *
     * @return \HopitalNumerique\UserBundle\Entity\User|null Référent
     */
    public function getRegionReferent(Reference $region)
    {
        $regionRemboursements = $this->remboursementManager->findBy(
            ['region' => $region],
            null,
            1
        );

        if (count($regionRemboursements) > 0) {
            return $regionRemboursements[0]->getReferent();
        }

        return null;
    }
}
