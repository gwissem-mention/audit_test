<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller\Commentaire;

use HopitalNumerique\CommunautePratiqueBundle\Controller\CommentaireController;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

/**
 * Contrôleur concernant les commentaires de groupe.
 */
class GroupeCommentaireController extends CommentaireController
{
    /**
     * Formulaire d'ajout d'un commentaire (sans template).
     */
    public function addAction(Groupe $groupe, Request $request)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
                ->canAccessGroupe($groupe)) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $nouveauCommentaire = $this->container
            ->get('hopitalnumerique_communautepratique.manager.commentaire')->createEmpty();
        $nouveauCommentaire->setGroupe($groupe);
        $nouveauCommentaire->setUser($this->getUser());

        return $this->editAction($nouveauCommentaire, $request);
    }
}
