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
        $datas = $this->get('hopitalnumerique_glossaire.manager.glossaire')->findGlossaireTable();

        return $this->render('HopitalNumeriqueGlossaireBundle:Front:index.html.twig', array(
            'datas' => $datas,
        ));
    }
}