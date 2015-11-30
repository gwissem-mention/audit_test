<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller\Commentaire;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;

/**
 * Contrôleur concernant les commentaires de fiche.
 */
class FicheCommentaireController extends Controller
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

        $commentaireForm = $this->createForm(
            'hopitalnumerique_communautepratiquebundle_commentaire',
            $commentaire,
            array(
                'redirectionRoute' => (null !== $commentaire->getId()
                    ? 'hopitalnumerique_communautepratique_commentaire_fichecommentaire_edit'
                    : 'hopitalnumerique_communautepratique_commentaire_fichecommentaire_add'),
                'redirectionRouteParams' => (null !== $commentaire->getId()
                    ? array('commentaire' => $commentaire->getId())
                    : array('fiche' => $commentaire->getFiche()->getId()))
            )
        );
        $commentaireForm->handleRequest($request);

        if ($commentaireForm->isSubmitted()) {
            if ($commentaireForm->isValid()) {
                $this->container->get('session')->getFlashBag()->add('success', 'Commentaire enregistré.');
                $this->container->get('hopitalnumerique_communautepratique.manager.commentaire')->save($commentaire);
            } else {
                $this->container->get('session')->getFlashBag()->add('danger', 'Commentaire non enregistré.');
            }

            return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_fiche_view', array('fiche' => $commentaire->getFiche()->getId())));
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Commentaire:edit.html.twig', array(
            'commentaire' => $commentaire,
            'commentaireForm' => $commentaireForm->createView()
        ));
    }
}
