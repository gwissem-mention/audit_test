<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller\Commentaire;

use HopitalNumerique\ObjetBundle\Controller\CommentaireController;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;

/**
 * Contrôleur concernant les commentaires de fiche.
 */
class FicheCommentaireController extends CommentaireController
{
    /**
     * Formulaire d'ajout d'un commentaire.
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

    /**
     * Formulaire d'ajout d'un commentaire.
     */
    public function editAction(Commentaire $commentaire, Request $request)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canEditCommentaire($commentaire)) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $commentaireForm = $this->createForm('hopitalnumerique_communautepratiquebundle_commentaire', $commentaire);
        $commentaireForm->handleRequest($request);
        
        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Commentaire/FicheCommentaire:edit.html.twig', array(
            'commentaire' => $commentaire,
            'commentaireForm' => $commentaireForm->createView()
        ));
    }
}
