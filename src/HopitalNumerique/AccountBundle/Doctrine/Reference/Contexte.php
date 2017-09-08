<?php

namespace HopitalNumerique\AccountBundle\Doctrine\Reference;

use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\DependencyInjection\ConnectedUser;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Connexion entre certaines références et le compte utilisateur.
 */
class Contexte
{
    /**
     * @var string Label de session de la demande de création de compte avec contexte
     */
    const WANT_CREATE_USER_SESSION_LABEL = 'hn_wantcreateuserwithcontexte';

    /**
     * @var SessionInterface Session
     */
    private $session;

    /**
     * @var ConnectedUser ConnectedUser
     */
    private $connectedUser;

    /**
     * @var ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * @var UserManager UserManager
     */
    private $userManager;

    /**
     * Constructeur.
     *
     * @param SessionInterface $session
     * @param ConnectedUser    $connectedUser
     * @param ReferenceManager $referenceManager
     * @param UserManager      $userManager
     */
    public function __construct(
        SessionInterface $session,
        ConnectedUser $connectedUser,
        ReferenceManager $referenceManager,
        UserManager $userManager
    ) {
        $this->session = $session;
        $this->connectedUser = $connectedUser;
        $this->referenceManager = $referenceManager;
        $this->userManager = $userManager;
    }

    /**
     * Retourne les ID des références du contexte utilisateur.
     *
     * @return array<integer> IDs des références
     */
    public function getReferenceIds()
    {
        $userContexteReferenceIds = [];

        if ($this->connectedUser->is()) {
            if (null !== $this->connectedUser->get()->getJobType()) {
                $userContexteReferenceIds[] = $this->connectedUser->get()
                    ->getJobType()
                    ->getId()
                ;
            }
            if (null !== $this->connectedUser->get()->getProfileType()) {
                $userContexteReferenceIds[] = $this->connectedUser->get()->getProfileType()->getId();
            }
            if (null !== $this->connectedUser->get()->getOrganizationType()) {
                $userContexteReferenceIds[] = $this->connectedUser->get()->getOrganizationType()->getId();
            }
            foreach ($this->connectedUser->get()->getActivities() as $activiteType) {
                $userContexteReferenceIds[] = $activiteType->getId();
            }
        }

        return $userContexteReferenceIds;
    }

    /**
     * Retourne la référence de la fonction si présente parmi les références.
     *
     * @param array<integer> $referenceIds IDs des références
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference|null Référence
     */
    public function getJobTypeByReferenceIds($referenceIds)
    {
        return $this->getReferenceByCodeAndIds('CONTEXTE_FONCTION_INTERNAUTE', $referenceIds);
    }

    /**
     * Retourne la référence du profil si présente parmi les références.
     *
     * @param array<integer> $referenceIds IDs des références
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference|null Référence
     */
    public function getProfilEtablissementSanteByReferenceIds($referenceIds)
    {
        return $this->getReferenceByCodeAndIds('CONTEXTE_METIER_INTERNAUTE', $referenceIds);
    }

    /**
     * Retourne la référence du statut si présente parmi les références.
     *
     * @param array<integer> $referenceIds IDs des références
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference|null Référence
     */
    public function getOrganizationTypeByReferenceIds($referenceIds)
    {
        return $this->getReferenceByCodeAndIds('CONTEXTE_TYPE_ES', $referenceIds);
    }

    /**
     * Retourne les références des types d'activité si présente parmi les références.
     *
     * @param array $referenceIds
     *
     * @return Reference[]
     */
    public function getActiviteTypesByReferenceIds($referenceIds)
    {
        return $this->getReferencesByCodeAndIds('CONTEXTE_SPECIALITE_ES', $referenceIds);
    }

    /**
     * Retourne la référence d'un code si présente parmi les références.
     *
     * @param string         $referenceCode Code
     * @param array<integer> $referenceIds  IDs des références
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference|null Référence
     */
    private function getReferenceByCodeAndIds($referenceCode, $referenceIds)
    {
        $possibleReferences = $this->referenceManager->findByCode($referenceCode);

        /** @var Reference $possibleReference */
        foreach ($possibleReferences as $possibleReference) {
            if (in_array($possibleReference->getId(), $referenceIds)) {
                return $possibleReference;
            }
        }

        return null;
    }

    /**
     * Retourne les références d'un code si présente parmi les références.
     *
     * @param string $referenceCode
     * @param array  $referenceIds
     *
     * @return Reference[]
     */
    private function getReferencesByCodeAndIds($referenceCode, $referenceIds)
    {
        $foundReferences = [];
        $possibleReferences = $this->referenceManager->findByCode($referenceCode);

        /** @var Reference $possibleReference */
        foreach ($possibleReferences as $possibleReference) {
            if (in_array($possibleReference->getId(), $referenceIds)) {
                $foundReferences[] = $possibleReference;
            }
        }

        return $foundReferences;
    }

    /**
     * Retourne un nouvel utilisateur avec les champs de contexte pré-remplis.
     *
     * @param array $referenceIds
     *
     * @return User
     */
    public function getNewUserWithContexte(array $referenceIds)
    {
        /** @var User $user */
        $user = $this->userManager->createEmpty();

        $this->fillUserWithContexte($user, $referenceIds);

        return $user;
    }

    /**
     * Sauvegarde le contexte d'un utilisateur.
     *
     * @param array $referenceIds
     *
     * @return bool Si l'utilisateur a été modifié
     */
    public function save(array $referenceIds)
    {
        $userIsModified = false;

        if ($this->connectedUser->is()) {
            $user = $this->connectedUser->get();

            $userIsModified = $this->fillUserWithContexte($user, $referenceIds);

            if ($userIsModified) {
                $this->userManager->save($user);
            }
        }

        return $userIsModified;
    }

    /**
     * Modifie les champs de contexte de l'utilisateur.
     *
     * @param User  $user
     * @param array $referenceIds
     *
     * @return bool Si l'utilisateur a été modifié
     */
    private function fillUserWithContexte(User &$user, array $referenceIds)
    {
        $userIsModified = false;

        $jobType = $this->getJobTypeByReferenceIds($referenceIds);
        $profilEtablissementSante = $this->getProfilEtablissementSanteByReferenceIds($referenceIds);
        $organizationType = $this->getOrganizationTypeByReferenceIds($referenceIds);
        $activiteTypes = $this->getActiviteTypesByReferenceIds($referenceIds);

        if (null !== $jobType
            && (null === $user->getJobType()
                || !$user->getJobType()->equals(
                    $jobType
                ))
        ) {
            $user->setJobType($jobType);
            $userIsModified = true;
        }

        if (null !== $profilEtablissementSante
            && (null === $user->getProfileType()
                || !$user->getProfileType()->equals(
                    $profilEtablissementSante
                ))
        ) {
            $user->setProfileType($profilEtablissementSante);
            $userIsModified = true;
        }

        if (null !== $organizationType
            && (null === $user->getOrganizationType()
                || !$user->getOrganizationType()->equals(
                    $organizationType
                ))
        ) {
            $user->setOrganizationType($organizationType);
            $userIsModified = true;
        }

        if (!$user->equalsActivities($activiteTypes)) {
            $user->setActivities($activiteTypes);
            $userIsModified = true;
        }

        return $userIsModified;
    }

    /**
     * Spécifie que l'utilisateur souhaite créer un compte avec le contexte choisi lors de la recherche.
     */
    public function setWantCreateUserWithContext()
    {
        $this->session->set(self::WANT_CREATE_USER_SESSION_LABEL, true);
    }

    /**
     * Retourne si l'utilisateur souhaite créer un compte avec son contexte.
     *
     * @return bool Si souhaite compte
     */
    public function isWantCreateUserWithContext()
    {
        return $this->session->get(self::WANT_CREATE_USER_SESSION_LABEL, false);
    }
}
