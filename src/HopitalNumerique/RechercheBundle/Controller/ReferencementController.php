<?php
namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Contrôleur de la recherche avancée.
 */
class ReferencementController extends Controller
{
    /**
     * Recherche avancée.
     */
    public function indexAction()
    {
        $currentDomaine = $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();
        $referencesTree = $this->container->get('hopitalnumerique_reference.dependency_injection.reference.tree')->getOrderedReferences(null, [$currentDomaine], true);

        return $this->render('HopitalNumeriqueRechercheBundle:Referencement:index.html.twig', [
            'referencesTree' => $referencesTree
        ]);
    }
}
