<?php
namespace HopitalNumerique\CommunautePratiqueBundle\DependencyInjection;

use Symfony\Component\Security\Core\SecurityContextInterface;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;

/**
 * Classe qui gère les accès / droits de la communauté de pratiques.
 */
class Security
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface SecurityContext
     */
    private $securityContext;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User|NULL Utilisateur connecté
     */
    private $user = null;


    /**
     * Constructeur.
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }


    /**
     * Retourne l'utilisateur courant.
     * Note : On utilise cette fonction car $user ne peut être créée dans le contrôleur car ce service est utilisé pour
     *     une extension Twig.
     *
     * @return \HopitalNumerique\UserBundle\Entity\User Utilisateur
     */
    private function getUser()
    {
        if (null === $this->user) {
            $this->user = (null !== $this->securityContext->getToken()
                ? ($this->securityContext->getToken()->getUser() instanceof User
                    ? $this->securityContext->getToken()->getUser()
                    : null)
                : null);
        }

        return $this->user;
    }


    /**
     * Retourne si l'utilisateur peut accéder à la communauté.
     *
     * @return boolean VRAI si accès autorisé
     */
    public function canAccessCommunautePratique()
    {
        return (null !== $this->getUser() && $this->getUser()->isInscritCommunautePratique());
    }

    /**
     * Retourne si un membre peut être désinscrit d'un groupe.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe Groupe
     * @return boolean VRAI si membre peut être désinscrit
     */
    public function canDeleteMembre(Groupe $groupe)
    {
        return ($this->canAddMembre($groupe));
    }

    /**
     * Retourne si un membre peut être ajouté au groupe.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe Groupe
     * @return boolean VRAI si membre ajoutable
     */
    public function canAddMembre(Groupe $groupe)
    {
        return ($this->canAccessCommunautePratique()
            && ( $groupe->hasAnimateur($this->getUser()) || $this->isAdmin() ));
    }

    /**
     * Retourne si l'utilisateur courant peut accéder à tel groupe.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe Groupe
     * @return boolean VRAI si groupe accessible
     */
    public function canAccessGroupe(Groupe $groupe)
    {
        return (
            $groupe->isEnCours()
            && (
                (null !== $this->getUser() && $this->getUser()->hasCommunautePratiqueGroupe($groupe))
                || $this->isAdmin()
            )
        );
    }

    /**
     * Retourne si l'utilisateur courant peut supprimer le document.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Document $document Document
     * @return boolean VRAI si document supprimable
     */
    public function canDeleteDocument(Document $document)
    {
        return (null !== $this->getUser() && $document->getUser()->getId() == $this->getUser()->getId());
    }

    /**
     * Retourne si l'utilisateur courant peut accéder à telle fiche.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiche Fiche
     * @return boolean VRAI si fiche accessible
     */
    public function canAccessFiche(Fiche $fiche)
    {
        return $this->canAccessGroupe($fiche->getGroupe());
    }

    /**
     * Retourne si l'utilisateur courant peut éditer telle fiche.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiche Fiche
     * @return boolean VRAI si fiche éditable
     */
    public function canEditFiche(Fiche $fiche)
    {
        return (
            (
                $this->canAccessFiche($fiche)
                && ($fiche->getUser()->getId() == $this->getUser()->getId()
                    || $fiche->getGroupe()->hasAnimateur($this->getUser())
                )
            )
            || $this->isAdmin()
        );
    }

    /**
     * Retourne si l'utilisateur courant peut supprimer tel fiche.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiche Fiche
     * @return boolean VRAI si fiche supprimable
     */
    public function canDeleteFiche(Fiche $fiche)
    {
        return $this->canEditFiche($fiche);
    }


    /**
     * Retourne si l'utilisateur courant peut résoudre telle fiche.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiche Fiche
     * @return boolean VRAI si fiche résolvable
     */
    public function canCloseFiche(Fiche $fiche)
    {
        return ($this->canEditFiche($fiche) && !$fiche->isResolu());
    }

    /**
     * Retourne si l'utilisateur courant peut rouvrir telle fiche.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiche Fiche
     * @return boolean VRAI si fiche rouvrable
     */
    public function canOpenFiche(Fiche $fiche)
    {
        return ($this->canEditFiche($fiche) && $fiche->isResolu());
    }

    /**
     * Retourne si l'utilisateur courant peut éditer tel commentaire.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire $commentaire Commentaire
     * @return boolean VRAI si commentaire éditable
     */
    public function canEditCommentaire(Commentaire $commentaire)
    {
        if (!$this->canAccessCommunautePratique()) {
            return false;
        }

        $groupe = (null !== $commentaire->getFiche() ? $commentaire->getFiche()->getGroupe() : $commentaire->getGroupe());

        return ($commentaire->getUser()->getId() == $this->getUser()->getId()
            || $groupe->hasAnimateur($this->getUser())
            || $this->isAdmin()
        );
    }

    /**
     * Retourne si l'utilisateur courant peut supprimer tel commentaire.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire $commentaire Commentaire
     * @return boolean VRAI si commentaire supprimable
     */
    public function canDeleteCommentaire(Commentaire $commentaire)
    {
        return $this->canEditCommentaire($commentaire);
    }


    /**
     * Retourne si l'utilisateur connecté est admin.
     *
     * @return boolean VRAI si admin
     */
    private function isAdmin()
    {
        return (null !== $this->getUser() && ($this->getUser()->hasRoleAdmin() || $this->getUser()->hasRoleAdminHn()));
    }
}
