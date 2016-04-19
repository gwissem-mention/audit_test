<?php
namespace HopitalNumerique\CommunautePratiqueBundle\DependencyInjection;

use HopitalNumerique\UserBundle\Entity\User;

/**
 * Classe qui gère les inscriptions à la communauté de pratique.
 */
class Inscription
{
    /**
     * Retourne s'il manque une information à l'utilisateur.
     * 
     * @param \HopitalNumerique\UserBundle\Entity\User $user Utilisateur
     * @return boolean VRAI si information manquante
     */
    public function hasInformationManquante(User $user)
    {
        return ( count($this->getInformationsManquantes($user)) > 0 );
    }

    /**
     * Retourne les informations manquantes pour se connecter à la communauté de pratique.
     * 
     * @param \HopitalNumerique\UserBundle\Entity\User $user Utilisateur
     * @return array<string> Informations manquantes
     */
    public function getInformationsManquantes(User $user)
    {
        $informationsManquantes = array();

        if (null === $user->getRegion() || null === $user->getDepartement()) {
            $informationsManquantes[] = 'Région & département';
        }
        if (null === $user->getEtablissementRattachementSante() && null === $user->getAutreStructureRattachementSante() && null === $user->getNomStructure()) {
            $informationsManquantes[] = 'Etablissement de rattachement / Nom de votre établissement / Nom de la structure';
        }
        if (null === $user->getProfilEtablissementSante()) {
            $informationsManquantes[] = 'Profil';
        }
        if (null === $user->getFonctionDansEtablissementSanteReferencement() && null === $user->getFonctionStructure()) {
            $informationsManquantes[] = 'Fonction';
        }

        return $informationsManquantes;
    }
}
