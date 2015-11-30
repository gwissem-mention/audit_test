<?php
namespace HopitalNumerique\CommunautePratiqueBundle\DependencyInjection;

use Symfony\Component\Security\Core\SecurityContextInterface;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;

/**
 * Classe qui gère les accès / droits de la communauté de pratiques.
 */
class Security
{
    /**
     * @var \HopitalNumerique\UserBundle\Entity\User|NULL Utilisateur connecté
     */
    private $user;


    /**
     * Constructeur.
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->user = (null !== $securityContext->getToken() ? ($securityContext->getToken()->getUser() instanceof User ? $securityContext->getToken()->getUser() : null) : null);
    }


    /**
     * Retourne si l'utilisateur peut accéder à la communauté.
     * 
     * @return boolean VRAI si accès autorisé
     */
    public function canAccessCommunautePratique()
    {
        return (null !== $this->user && $this->user->isInscritCommunautePratique());
    }

    /**
     * Retourne si l'utilisateur courant peut accéder à tel groupe.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe Groupe
     * @return boolean VRAI si groupe accessible
     */
    public function canAccessGroupe(Groupe $groupe)
    {
        return (null !== $this->user && $this->user->hasCommunautePratiqueGroupe($groupe));
    }

    /**
     * Retourne si l'utilisateur courant peut supprimer le document.
     * 
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Document $document Document
     * @return boolean VRAI si document supprimable
     */
    public function canDeleteDocument(Document $document)
    {
        return (null !== $this->user && $document->getUser()->getId() == $this->user->getId());
    }
}
