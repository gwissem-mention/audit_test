<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Contrôleur concernant les groupes de la communauté de pratique.
 */
class GroupeController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affiche tous les groupes disponibles.
     */
    public function listAction(Request $request)
    {
        $domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')->findOneById($request->getSession()->get('domaineId'));

        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Groupe:list.html.twig',
            array
            (
                'groupesNonDemarres' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->findNonDemarres($domaine),
                'groupesEnCours' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->findEnCours($domaine)
            )
        );
    }
}
