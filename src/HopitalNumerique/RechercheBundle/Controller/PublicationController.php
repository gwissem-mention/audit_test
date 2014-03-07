<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PublicationController extends Controller
{
    /**
     * Objet Action
     */
    public function objetAction($id, $alias)
    {
        

        return $this->render('HopitalNumeriqueRechercheBundle:Publication:objet.html.twig', array(
            
        ));
    }

    /**
     * Contenu Action
     */
    public function contenuAction($id, $alias, $idc, $aliasc)
    {
        

        return $this->render('HopitalNumeriqueRechercheBundle:Publication:contenu.html.twig', array(
            
        ));
    }
}