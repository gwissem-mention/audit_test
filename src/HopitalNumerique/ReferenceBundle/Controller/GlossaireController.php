<?php
namespace HopitalNumerique\ReferenceBundle\Controller;

use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur du glossaire.
 */
class GlossaireController extends Controller
{
    /**
     * Liste du glossaire du domaine courant.
     */
    public function listAction()
    {
        $glossaire = $this->container->get('hopitalnumerique_reference.doctrine.glossaire.reader')->getGlossaireGroupedByLetterByDomaine($this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get());

        return $this->render('HopitalNumeriqueReferenceBundle:Glossaire:list.html.twig', [
            'glossaire' => $glossaire
        ]);
    }

    /**
     * Liste du glossaire complet.
     */
    public function listFullAction()
    {
        $glossaire = $this->container->get('hopitalnumerique_reference.doctrine.glossaire.reader')->getGlossaireGroupedByLetter();

        return $this->render('HopitalNumeriqueReferenceBundle:Glossaire:list.html.twig', [
            'glossaire' => $glossaire
        ]);
    }

    /**
     * Migre l'ancien glossaire.
     * /admin/glossaire/migre/troispetitschapeaudepaillaisson
     */
    public function migreAction($token)
    {
        if ('troispetitschapeaudepaillaisson' == $token) {
            $this->container->get('hopitalnumerique_reference.doctrine.glossaire.migration')->execute();

            return new Response('OK');
        }

        return new Response(':(');
    }

    /**
     * Parse toutes les publications.
     */
    public function parseAction()
    {
        set_time_limit(0);
        $this->container->get('hopitalnumerique_reference.doctrine.glossaire.parse')->parseAndSaveAll();

        return new Response('OK');
    }

    /**
     * Fenêtre d'un élément du glossaire.
     */
    public function popinAction(Reference $glossaireReference)
    {
        return $this->render('HopitalNumeriqueReferenceBundle:Glossaire:popin.html.twig', [
            'glossaireReference' => $glossaireReference
        ]);
    }
}
