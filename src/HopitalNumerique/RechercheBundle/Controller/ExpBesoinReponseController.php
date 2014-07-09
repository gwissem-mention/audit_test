<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use HopitalNumerique\RechercheBundle\Entity\ExpBesoin;
use HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses;

class ExpBesoinReponseController extends Controller
{
    public function indexAction(ExpBesoin $expBesoin)
    {
        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:index.html.twig', array(
                'expBesoin' => $expBesoin
            ));    
    }

    public function addAction()
    {
        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:add.html.twig', array(
                // ...
            ));    
    }

    public function deleteAction()
    {
        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:delete.html.twig', array(
                // ...
            ));    
    }

    public function editAction()
    {
        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:edit.html.twig', array(
                // ...
            ));    
    }

    public function saveAction()
    {
        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:save.html.twig', array(
                // ...
            ));    
    }

    public function reorderAction()
    {
        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:reorder.html.twig', array(
                // ...
            ));    
    }

}
