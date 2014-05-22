<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;
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
     *
     * @param Outil $outil L'entitée Outil
     */
    public function outilAction( Outil $outil )
    {
        //init some vars
        $ponderationMax = 100;
        $questions      = array();
        $chapitres      = $outil->getChapitres();
        
        //build big array of questions
        foreach($chapitres as $chapitre)
            $questions = array_merge($questions, $chapitre->getQuestions()->toArray() );
        
        //calcul pondération
        foreach( $questions as $key => $question) {
            $ponderation = $question->getPonderation();

            if( $ponderation != 0 ){
                $ponderationMax -= $ponderation;
                unset( $questions[$key] );
            }
        }

        //max Pondération invalid
        if( $ponderationMax != 0 ) {
            // On envoi une 'flash' pour indiquer à l'utilisateur que l'outil à été enregistré
            $this->get('session')->getFlashBag()->add( 'danger', 'L\'outil n\'est pas correctement configué, il n\'est donc pas accessible');
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage'));
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:outil.html.twig' , array(
            'outil' => $outil
        ));
    }

    /**
     * Sauvegarde du formulaire outil (save OU valid)
     *
     * @param Outil   $outil   L'entitée Outil
     * @param Request $request La requete
     */
    public function saveAction( Outil $outil, Request $request )
    {
        //get posted Datas
        $chapitres   = $request->request->get( $outil->getAlias() );
        $remarques   = $request->request->get( 'remarques-' . $outil->getAlias() );
        $action      = $request->request->get('action');
        $remplissage = $request->request->get('remplissage');

        //try to get the connected user
        $user = $this->get('security.context')->getToken()->getUser();
        $user = $user != 'anon.' ? $user : false;

        //create Resultat entity
        $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->createEmpty();
        $resultat->setStatut( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 418) ) );
        $resultat->setOutil( $outil );
        $resultat->setTauxRemplissage( $remplissage );

        //cas ou l'user à validé le questionnaire
        if( $action == 'valid')
            $resultat->setDateValidation( new \DateTime() );

        //cas user connecté
        if( $user )
            $resultat->setUser( $user );

        $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultat );

        //Save Réponses
        $reponses = array();
        foreach($chapitres as $chapitre => $questions) {
            foreach($questions as $id => $value) {
                //get entity Question
                $question = $this->get('hopitalnumerique_autodiag.manager.question')->findOneBy( array('id' => $id ) );

                //build remarque
                $remarque = ( isset($remarques[$chapitre]) && isset($remarques[$chapitre][$id]) ) ? $remarques[$chapitre][$id] : '';

                //create entity Reponse
                $reponse = $this->get('hopitalnumerique_autodiag.manager.reponse')->createEmpty();
                $reponse->setQuestion( $question );
                $reponse->setResultat( $resultat );
                $reponse->setRemarque( $remarque );
                $reponse->setValue( $value );

                $reponses[] = $reponse;
            }
        }
        $this->get('hopitalnumerique_autodiag.manager.reponse')->save( $reponses );

        // On envoi une 'flash' pour indiquer à l'utilisateur que l'outil à été enregistré
        $this->get('session')->getFlashBag()->add( 'success', 'Autodiagnostic ' . ($action == 'valid' ? 'validé':'enregistré') );

        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_resultat', array( 'id' => $resultat->getId() ) ) );
    }

    /**
     * Affiche les résultat d'un outil après la validation d'un outil en front
     *
     * @param  Resultat $resultat L'entitée résultat
     */
    public function resultatAction( Resultat $resultat )
    {


        
        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:resultat.html.twig' , array(
            'resultat' => $resultat
        ));
    }
}