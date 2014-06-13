<?php

namespace HopitalNumerique\UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * RefusCandidature controller.
 */
class RefusCandidatureController extends Controller
{
    /**
     * Affiche la liste des RefusCandidature.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_user.grid.refuscandidature');

        return $grid->render('HopitalNumeriqueUserBundle:RefusCandidature:index.html.twig');
    }

    /**
     * Affiche le RefusCandidature en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de RefusCandidature.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $refuscandidature = $this->get('hopitalnumerique_user.manager.refuscandidature')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueUserBundle:RefusCandidature:show.html.twig', array(
            'refuscandidature' => $refuscandidature,
        ));
    }
}