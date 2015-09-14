<?php

namespace HopitalNumerique\GlossaireBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Front controller.
 */
class FrontController extends Controller
{
    /**
     * Affiche le Glossaire
     */
    public function indexAction()
    {
        $glossaires = $this->get('hopitalnumerique_glossaire.manager.glossaire')->findGlossaireTable($this->get('request')->getSession()->get('domaineId'));

        return $this->render('HopitalNumeriqueGlossaireBundle:Front:index.html.twig', array(
            'glossaires' => $glossaires,
        ));
    }
}