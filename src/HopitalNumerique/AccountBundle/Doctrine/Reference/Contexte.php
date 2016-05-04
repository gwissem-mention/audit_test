<?php
namespace HopitalNumerique\AccountBundle\Doctrine\Reference;

use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\DependencyInjection\ConnectedUser;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Connexion entre certaines références et le compte utilisateur.
 */
class Contexte
{
    /**
     * @var \HopitalNumerique\UserBundle\DependencyInjection\ConnectedUser ConnectedUser
     */
    private $connectedUser;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager UserManager
     */
    private $userManager;


    /**
     * Constructeur.
     */
    public function __construct(ConnectedUser $connectedUser, ReferenceManager $referenceManager, UserManager $userManager)
    {
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
            if (null !== $this->connectedUser->get()->getFonctionDansEtablissementSanteReferencement()) {
                $userContexteReferenceIds[] = $this->connectedUser->get()->getFonctionDansEtablissementSanteReferencement()->getId();
            }
            if (null !== $this->connectedUser->get()->getProfilEtablissementSante()) {
                $userContexteReferenceIds[] = $this->connectedUser->get()->getProfilEtablissementSante()->getId();
            }
            if (null !== $this->connectedUser->get()->getStatutEtablissementSante()) {
                $userContexteReferenceIds[] = $this->connectedUser->get()->getStatutEtablissementSante()->getId();
            }
            foreach ($this->connectedUser->get()->getTypeActivite() as $activiteType) {
                $userContexteReferenceIds[] = $activiteType->getId();
            }
        }

        return $userContexteReferenceIds;
    }


    /**
     * Retourne la référence de la fonction si présente parmi les références.
     *
     * @param array<integer> $referenceIds IDs des références
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference|null Référence
     */
    public function getFonctionDansEtablissementSanteReferencementByReferenceIds($referenceIds)
    {
        return $this->getReferenceByCodeAndIds('CONTEXTE_FONCTION_INTERNAUTE', $referenceIds);
    }

    /**
     * Retourne la référence du profil si présente parmi les références.
     *
     * @param array<integer> $referenceIds IDs des références
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
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference|null Référence
     */
    public function getStatutEtablissementSanteByReferenceIds($referenceIds)
    {
        return $this->getReferenceByCodeAndIds('CONTEXTE_TYPE_ES', $referenceIds);
    }

    /**
     * Retourne les références des types d'activité si présente parmi les références.
     *
     * @param array<integer> $referenceIds IDs des références
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference|null Référence
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
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference|null Référence
     */
    private function getReferenceByCodeAndIds($referenceCode, $referenceIds)
    {
        $possibleReferences = $this->referenceManager->findByCode($referenceCode);

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
     * @param string         $referenceCode Code
     * @param array<integer> $referenceIds  IDs des références
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Références
     */
    private function getReferencesByCodeAndIds($referenceCode, $referenceIds)
    {
        $foundReferences = [];
        $possibleReferences = $this->referenceManager->findByCode($referenceCode);

        foreach ($possibleReferences as $possibleReference) {
            if (in_array($possibleReference->getId(), $referenceIds)) {
                $foundReferences[] = $possibleReference;
            }
        }

        return $foundReferences;
    }


    /**
     * Sauvegarde le contexte d'un utilisateur.
     *
     * @param array<integer> $referenceIds IDs des références
     * @return boolean Si l'utilisateur a été modifié
     */
    public function save(array $referenceIds)
    {
        $userIsModified = false;

        if ($this->connectedUser->is()) {
            $user = $this->connectedUser->get();

            $fonctionDansEtablissementSanteReferencement = $this->getFonctionDansEtablissementSanteReferencementByReferenceIds($referenceIds);
            $profilEtablissementSante = $this->getProfilEtablissementSanteByReferenceIds($referenceIds);
            $statutEtablissementSante = $this->getStatutEtablissementSanteByReferenceIds($referenceIds);
            $activiteTypes = $this->getActiviteTypesByReferenceIds($referenceIds);

            if (null !== $fonctionDansEtablissementSanteReferencement && (null === $user->getFonctionDansEtablissementSanteReferencement() || !$user->getFonctionDansEtablissementSanteReferencement()->equals($fonctionDansEtablissementSanteReferencement))) {
                $user->setFonctionDansEtablissementSanteReferencement($fonctionDansEtablissementSanteReferencement);
                $userIsModified = true;
            }
            if (null !== $profilEtablissementSante && (null === $user->getProfilEtablissementSante() || !$user->getProfilEtablissementSante()->equals($profilEtablissementSante))) {
                $user->setProfilEtablissementSante($profilEtablissementSante);
                $userIsModified = true;
            }
            if (null !== $statutEtablissementSante && (null === $user->getStatutEtablissementSante() || !$user->getStatutEtablissementSante()->equals($statutEtablissementSante))) {
                $user->setStatutEtablissementSante($statutEtablissementSante);
                $userIsModified = true;
            }
            if (!$user->equalsTypeActivite($activiteTypes)) {
                $user->setTypeActivites($activiteTypes);
                $userIsModified = true;
            }
        }

        if ($userIsModified) {
            $this->userManager->save($user);
        }

        return $userIsModified;
    }
}
