<?php

namespace HopitalNumerique\GlossaireBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
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
        $glossaires = $this->get('hopitalnumerique_glossaire.manager.glossaire')->findAll();

        $datas = array();
        foreach($glossaires as $one){
            $firstL = substr( ucfirst($one->getMot()), 0, 1);
            $datas[ $firstL ][] = $one;
        }

        return $this->render('HopitalNumeriqueGlossaireBundle:Front:index.html.twig', array(
            'datas' => $datas,
        ));
    }
}