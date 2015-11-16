<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Twig\Extension;

use HopitalNumerique\CommunautePratiqueBundle\DependencyInjection\Inscription;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Ajout de filtres Twig.
 */
class FilterExtension extends \Twig_Extension
{
    /**
     * @var \HopitalNumerique\CommunautePratiqueBundle\DependencyInjection\Inscription Service Inscription
     */
    private $inscriptionService;


    /**
     * Constructeur.
     */
    public function __construct(Inscription $inscriptionService)
    {
        $this->inscriptionService = $inscriptionService;
    }


    /**
     * (non-PHPdoc)
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return array
        (
            'communautePratiqueHasInformationManquante' => new \Twig_Filter_Method($this, 'communautePratiqueHasInformationManquante'),
            'communautePratiqueGetInformationsManquantes' => new \Twig_Filter_Method($this, 'communautePratiqueGetInformationsManquantes')
        );
    }

    
    /**
     * Retourne s'il manque une information à l'utilisateur.
     * 
     * @param \HopitalNumerique\UserBundle\Entity\User $user Utilisateur
     * @return boolean VRAI si information manquante
     */
    public function communautePratiqueHasInformationManquante(User $user)
    {
        return $this->inscriptionService->hasInformationManquante($user);
    }

    /**
     * Retourne les informations manquantes pour se connecter à la communauté de pratique.
     * 
     * @param \HopitalNumerique\UserBundle\Entity\User $user Utilisateur
     * @return array<string> Informations manquantes
     */
    public function communautePratiqueGetInformationsManquantes(User $user)
    {
        return $this->inscriptionService->getInformationsManquantes($user);
    }
    
    /**
     * (non-PHPdoc)
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return 'hopitalnumerique_communautepratique.twig.extension.filter';
    }
}
