<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Front controller.
 */
class FrontController extends Controller
{
    /**
     * Affiche le Front vu chapitre
     */
    public function outilAction( Outil $outil )
    {
        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:outil.html.twig' , array(
            'outil' => $outil
        ));
    }

    /**
     * Sauvegarde du formulaire outil (save OU valid)
     */
    public function saveAction( Outil $outil, Request $request )
    {
        //get posted Datas
        $chapitres = $request->request->get( $outil->getAlias() );
        $action    = $request->request->get('action');

        //try to get the connected user
        $user = $this->get('security.context')->getToken()->getUser();
        $user = $user != 'anon.' ? $user : false;

        //create Resultat entity
        $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->createEmpty();
        $resultat->setStatut( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 418) ) );
        $resultat->setOutil( $outil );

        //cas ou l'user à validé le questionnaire
        if( $action == 'valid')
            $resultat->setDateValidation( new \DateTime() );

        //cas user connecté
        if( $user )
            $resultat->setUser( $user );

        $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultat );

        //Save Réponses
        foreach($chapitres as $chapitre) {

        }







        die('save');
    }
}