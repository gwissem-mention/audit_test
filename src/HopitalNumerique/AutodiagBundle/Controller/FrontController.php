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
        $parents        = array();
        $enfants        = array();
        
        //build big array of questions
        foreach($chapitres as $chapitre){
            $questions = array_merge($questions, $chapitre->getQuestions()->toArray() );

            if( is_null($chapitre->getParent()) ){
                $parents[ $chapitre->getId() ]['parent'] = $chapitre;
                $parents[ $chapitre->getId() ]['childs'] = array();
            }else
                $enfants[] = $chapitre;
        }
        
        //calcul pondération
        foreach( $questions as $key => $question) {
            $ponderation = $question->getPonderation();

            if( $ponderation != 0 )
                $ponderationMax -= $ponderation;
        }

        //max Pondération invalid
        if( $ponderationMax != 0 ) {
            // On envoi une 'flash' pour indiquer à l'utilisateur que l'outil à été enregistré
            $this->get('session')->getFlashBag()->add( 'danger', 'L\'outil n\'est pas correctement configué, il n\'est donc pas accessible');
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage'));
        }

        //reformate les chapitres
        foreach($enfants as $enfant) {
            $parentId = $enfant->getParent()->getId();
            $parents[ $parentId ]['childs'][] = $enfant;
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:outil.html.twig' , array(
            'outil'     => $outil,
            'chapitres' => $parents
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
        $resultat->setOutil( $outil );
        $resultat->setTauxRemplissage( $remplissage );

        //cas ou l'user à validé le questionnaire
        if( $action == 'valid'){
            $resultat->setDateValidation( new \DateTime() );
            $resultat->setStatut( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 419) ) );
        }else
            $resultat->setStatut( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 418) ) );

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