<?php
namespace HopitalNumerique\ReferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur du glossaire.
 */
class GlossaireController extends Controller
{
    /**
     * Liste du glossaire.
     */
    public function indexAction()
    {
        $glossaire = $this->container->get('hopitalnumerique_reference.doctrine.glossaire.reader')->getGlossaireGroupedByLetterByDomaine($this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get());

        return $this->render('HopitalNumeriqueReferenceBundle:Glossaire:index.html.twig', [
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
}
