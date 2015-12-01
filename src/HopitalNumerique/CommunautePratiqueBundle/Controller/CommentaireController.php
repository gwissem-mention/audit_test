<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Contrôleur concernant les commentaires.
 */
class CommentaireController extends Controller
{
    /**
     * Formulaire d'ajout d'un commentaire (sans template).
     */
    public function editAction(Commentaire $commentaire, Request $request)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canEditCommentaire($commentaire)) {
            throw new \Exception('Commentaire non éditable.');
        }

        $commentaireForm = $this->container
            ->get('hopitalnumerique_communautepratique.dependency_injection.commentaire')
            ->getForm($commentaire, $request);

        if ($commentaireForm->isSubmitted()) {
            if ($commentaireForm->isValid()) {
                $this->container->get('session')->getFlashBag()->add('success', 'Commentaire enregistré.');
                $this->container->get('hopitalnumerique_communautepratique.manager.commentaire')->save($commentaire);
            } else {
                $this->container->get('session')->getFlashBag()->add('danger', 'Commentaire non enregistré.');
            }

            return $this->redirect($this->container
                ->get('hopitalnumerique_communautepratique.dependency_injection.commentaire')
                ->getRedirectionUrl($commentaire));
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Commentaire:edit.html.twig', array(
            'commentaire' => $commentaire,
            'commentaireForm' => $commentaireForm->createView()
        ));
    }

    /**
     * Supprime le commentaire.
     */
    public function deleteAction(Commentaire $commentaire)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canDeleteCommentaire($commentaire)) {
            return new JsonResponse(array('success' => false));
        }

        $this->container->get('hopitalnumerique_communautepratique.manager.commentaire')->delete($commentaire);

        return new JsonResponse(array('success' => true));
    }
}
