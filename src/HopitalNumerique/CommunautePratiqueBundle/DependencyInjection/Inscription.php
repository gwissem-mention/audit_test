<?php

namespace HopitalNumerique\CommunautePratiqueBundle\DependencyInjection;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Translation\Translator;

/**
 * Classe qui gère les inscriptions à la communauté de pratique.
 */
class Inscription
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * Inscription constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Retourne s'il manque une information à l'utilisateur.
     *
     * @param User $user Utilisateur
     *
     * @return bool VRAI si information manquante
     */
    public function hasInformationManquante(User $user)
    {
        return  count($this->getInformationsManquantes($user)) > 0;
    }

    /**
     * Retourne les informations manquantes pour se connecter à la communauté de pratique.
     *
     * @param User $user Utilisateur
     *
     * @return array<string> Informations manquantes
     */
    public function getInformationsManquantes(User $user)
    {
        $informationsManquantes = [];

        if (empty(trim($user->getLastname())) || empty(trim($user->getFirstname()))) {
            $informationsManquantes[] = 'Nom & Prénom';
        }

        if (null === $user->getRegion() || null === $user->getCounty()) {
            $informationsManquantes[] = 'Région & département';
        }

        if (null === $user->getOrganization()
            && null === $user->getOrganizationLabel()
        ) {
            $informationsManquantes[] = 'Structure de rattachement / Nom de votre établissement / Nom de la structure';
        }

        if (null === $user->getProfileType()) {
            $informationsManquantes[] = 'Profil';
        }

        if (null === $user->getJobType()
            && null === $user->getJobLabel()
        ) {
            $informationsManquantes[] = 'Fonction';
        }

        return $informationsManquantes;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getMissingInformationByTab(User $user)
    {
        $tab1Label = $this->translator->trans('account.profile.personal_information');
        $tab2Label = $this->translator->trans('account.profile.contact_information');
        $tab3Label = $this->translator->trans('account.profile.title');
        $tab4Label = $this->translator->trans('account.profile.structure');
        $tab5Label = $this->translator->trans('account.profile.skills');

        $missingInformation = [
            $tab1Label => [],
            $tab2Label => [],
            $tab3Label => [],
            $tab4Label => [],
            $tab5Label => [],
        ];

        $missingInformationCount = 0;

        if (is_null($user->getFirstname())) {
            $missingInformation[$tab1Label][] = $this->translator->trans('account.user.firstname');
            $missingInformationCount++;
        }

        if (is_null($user->getLastname())) {
            $missingInformation[$tab1Label][] = $this->translator->trans('account.user.lastname');
            $missingInformationCount++;
        }

        if (is_null($user->getRegion())) {
            $missingInformation[$tab4Label][] = $this->translator->trans('account.user.region');
            $missingInformationCount++;
        }

        if (is_null($user->getCounty())) {
            $missingInformation[$tab4Label][] = $this->translator->trans('account.user.county');
            $missingInformationCount++;
        }

        if (is_null($user->getOrganization())
            && is_null($user->getOrganizationLabel())
        ) {
            $missingInformation[$tab4Label][] = $this->translator->trans('account.user.organization');
            $missingInformationCount++;
        }

        if (is_null($user->getProfileType())) {
            $missingInformation[$tab3Label][] = $this->translator->trans('account.user.profileType');
            $missingInformationCount++;
        }

        if (is_null($user->getJobType())) {
            $missingInformation[$tab3Label][] = $this->translator->trans('account.user.jobType');
            $missingInformationCount++;
        }

        if (is_null($user->getJobLabel())) {
            $missingInformation[$tab3Label][] = $this->translator->trans('account.user.jobLabel');
            $missingInformationCount++;
        }

        if ($missingInformationCount == 0) {
            return [];
        }

        return $missingInformation;
    }
}
