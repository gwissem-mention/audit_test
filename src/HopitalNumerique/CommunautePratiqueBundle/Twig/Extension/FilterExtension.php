<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Twig\Extension;

use HopitalNumerique\CommunautePratiqueBundle\DependencyInjection\Inscription;
use HopitalNumerique\CommunautePratiqueBundle\DependencyInjection\Security;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;

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
     * @var \HopitalNumerique\CommunautePratiqueBundle\DependencyInjection\Security Service Security
     */
    private $securityService;


    /**
     * Constructeur.
     */
    public function __construct(Inscription $inscriptionService, Security $securityService)
    {
        $this->inscriptionService = $inscriptionService;
        $this->securityService = $securityService;
    }


    /**
     * (non-PHPdoc)
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return array
        (
            'communautePratiqueHasInformationManquante' => new \Twig_Filter_Method($this, 'hasInformationManquante'),
            'communautePratiqueGetInformationsManquantes' => new \Twig_Filter_Method($this, 'getInformationsManquantes'),
            'communautePratiqueCanEdit' => new \Twig_Filter_Method($this, 'canEdit'),
            'communautePratiqueCanDelete' => new \Twig_Filter_Method($this, 'canDelete')
        );
    }


    /**
     * Retourne s'il manque une information à l'utilisateur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user Utilisateur
     * @return boolean VRAI si information manquante
     */
    public function hasInformationManquante(User $user)
    {
        return $this->inscriptionService->hasInformationManquante($user);
    }

    /**
     * Retourne les informations manquantes pour se connecter à la communauté de pratique.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user Utilisateur
     * @return array<string> Informations manquantes
     */
    public function getInformationsManquantes(User $user)
    {
        return $this->inscriptionService->getInformationsManquantes($user);
    }

    /**
     * Retourne si un objet peut être édité.
     *
     * @param object $object Entité
     * @return boolean VRAI si éditable
     */
    public function canEdit($object)
    {
        if ($object instanceof Commentaire) {
            return $this->securityService->canEditCommentaire($object);
        }

        throw new \Exception('Méthode "communautePratiqueCanEdit" non implémentée pour ce type d\'objet');
    }

    /**
     * Retourne si un objet peut être supprimé.
     *
     * @param object $object Entité
     * @return boolean VRAI si supprimable
     */
    public function canDelete($object)
    {
        if ($object instanceof Commentaire) {
            return $this->securityService->canDeleteCommentaire($object);
        }

        throw new \Exception('Méthode "communautePratiqueCanDelete" non implémentée pour ce type d\'objet');
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
