<?php

namespace HopitalNumerique\CommunautePratiqueBundle\DependencyInjection;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire as CommentaireEntity;

/**
 * Classe qui gère les commentaires de la communauté de pratique.
 */
class Commentaire
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface Router
     */
    private $router;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface FormFactory
     */
    private $formFactory;

    /**
     * Constructeur.
     */
    public function __construct(RouterInterface $router, FormFactoryInterface $formFactory)
    {
        $this->router = $router;
        $this->formFactory = $formFactory;
    }

    /**
     * Retourne le formulaire traité avec le Request.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\DependencyInjection\Commentaire $commentaire Commentaire
     * @param \Symfony\Component\HttpFoundation\Request                                  $request     Request
     *
     * @return \Symfony\Component\Form\FormInterface Formulaire
     */
    public function getForm(CommentaireEntity $commentaire, Request $request)
    {
        if (null !== $commentaire->getFiche()) {
            $commentaireFormOptions = [
                'redirectionRoute' => (null !== $commentaire->getId()
                    ? 'hopitalnumerique_communautepratique_commentaire_edit'
                    : 'hopitalnumerique_communautepratique_commentaire_fichecommentaire_add'),
                'redirectionRouteParams' => (null !== $commentaire->getId()
                    ? ['commentaire' => $commentaire->getId()]
                    : ['fiche' => $commentaire->getFiche()->getId()]),
            ];
        } else {
            $commentaireFormOptions = [
                'redirectionRoute' => (null !== $commentaire->getId()
                    ? 'hopitalnumerique_communautepratique_commentaire_edit'
                    : 'hopitalnumerique_communautepratique_commentaire_groupecommentaire_add'),
                'redirectionRouteParams' => (null !== $commentaire->getId()
                    ? ['commentaire' => $commentaire->getId()]
                    : ['groupe' => $commentaire->getGroupe()->getId()]),
            ];
        }

        $commentaireForm = $this->formFactory->create(
            'hopitalnumerique_communautepratiquebundle_commentaire',
            $commentaire,
            $commentaireFormOptions
        );
        $commentaireForm->handleRequest($request);

        return $commentaireForm;
    }

    /**
     * Retourne l'URL où rediriger l'utilisateur.
     *
     * @return string URL
     */
    public function getRedirectionUrl(CommentaireEntity $commentaire)
    {
        if (null !== $commentaire->getFiche()) {
            return $this->router->generate(
                'hopitalnumerique_communautepratique_fiche_view',
                ['fiche' => $commentaire->getFiche()->getId()]
            );
        } else {
            return $this->router->generate(
                'hopitalnumerique_communautepratique_groupe_view',
                ['groupe' => $commentaire->getGroupe()->getId()]
            );
        }
    }
}
