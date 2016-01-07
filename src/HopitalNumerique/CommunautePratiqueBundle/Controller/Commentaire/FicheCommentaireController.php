<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller\Commentaire;

use HopitalNumerique\CommunautePratiqueBundle\Controller\CommentaireController;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;

/**
 * Contrôleur concernant les commentaires de fiche.
 */
class FicheCommentaireController extends CommentaireController
{
    /**
     * Formulaire d'ajout d'un commentaire (sans template).
     */
    public function addAction(Fiche $fiche, Request $request)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
                ->canAccessFiche($fiche)) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $nouveauCommentaire = $this->container
            ->get('hopitalnumerique_communautepratique.manager.commentaire')->createEmpty();
        $nouveauCommentaire->setFiche($fiche);
        $nouveauCommentaire->setUser($this->getUser());

        return $this->editAction($nouveauCommentaire, $request);
    }
}
