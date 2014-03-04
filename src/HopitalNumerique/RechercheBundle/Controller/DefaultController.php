<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        $elements = $this->get('hopitalnumerique_reference.manager.reference')->getArboFormat(false, false, true);

        return $this->render('HopitalNumeriqueRechercheBundle:Default:index.html.twig', array(
            'elements' => $elements['CATEGORIES_RECHERCHE']
        ));
    }
}