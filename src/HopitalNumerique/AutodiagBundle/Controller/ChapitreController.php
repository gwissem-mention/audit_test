<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Chapitre controller.
 */
class ChapitreController extends Controller
{
    /**
     * Affiche la liste des chapitres.
     */
    public function indexAction(Outil $outil)
    {
        
        return $this->render( 'HopitalNumeriqueAutodiagBundle:Chapitre:index.html.twig' , array(
            'outil'     => $outil,
            'chapitres' => array()
        ));
    }

    /**
     * Ajoute un chapitre
     */
    public function addAction(Outil $outil)
    {
        //créer un chapitre
        $chapitre = $this->get('hopitalnumerique_autodiag.manager.chapitre')->createEmpty();
        $chapitre->setOutil( $outil );

        //guess order
        $order = $this->get('hopitalnumerique_autodiag.manager.chapitre')->countChapitres($outil) + 1;
        $chapitre->setOrder( $order );

        return $this->render('HopitalNumeriqueAutodiagBundle:Chapitre:add.html.twig', array(
            'chapitre' => $chapitre
        ));
    }


}