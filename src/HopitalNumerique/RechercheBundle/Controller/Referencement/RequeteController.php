<?php
namespace HopitalNumerique\RechercheBundle\Controller\Referencement;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Contrôleur concernant la sauvegarde des requêtes de la recherche par référencement.
 */
class RequeteController extends Controller
{
    /**
     * Popin de sauvegarde.
     */
    public function popinSaveAction(Request $request)
    {
        $referenceIds = $request->request->get('referenceIds', []);

        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setReferenceIds($referenceIds);

        return $this->render('HopitalNumeriqueRechercheBundle:Referencement\Requete:popin_save.html.twig');
    }
}
