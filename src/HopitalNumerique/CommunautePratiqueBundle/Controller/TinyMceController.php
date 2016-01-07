<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

/**
 * Plugins pour TinyMCE.
 */
class TinyMceController extends Controller
{
    /**
     * Popup avec la liste des documents d'un groupe.
     */
    public function documentsAction(Groupe $groupe, Request $request)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canAccessGroupe($groupe)) {
            throw new \Exception('Accès non autorisé.');
        }

        $documents = $this->container->get('hopitalnumerique_communautepratique.manager.document')
            ->findBy(array('groupe' => $groupe, 'user' => $this->getUser()));

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:TinyMce:documents.html.twig', array(
            'documents' => $documents,
            'texteSelectionne' => $request->request->get('texteSelectionne')
        ));
    }
}
