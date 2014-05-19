<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Chapitre;
use HopitalNumerique\AutodiagBundle\Entity\Question;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Question controller.
 */
class QuestionController extends Controller
{
    /**
     * Affiche la liste des question pour le chapitre selectioné.
     */
    public function indexAction(Chapitre $chapitre)
    {
        $questions = $this->get('hopitalnumerique_autodiag.manager.question')->findBy( array('chapitre' => $chapitre ) );

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Question:index.html.twig' , array(
            'questions' => $questions,
            'chapitre'  => $chapitre
        ));
    }

    /**
     * Suppresion d'une question.
     *
     * METHOD = POST|DELETE
     */
    public function deleteAction( Question $question )
    {
        //get chapitre
        $chapitre = $question->getChapitre();
        
        //delete
        $this->get('hopitalnumerique_autodiag.manager.question')->delete( $question );

        //On recherche si le parent de l'élément que l'on delete à encore des enfants après cette supression
        $stillHaveChilds = $this->get('hopitalnumerique_autodiag.manager.question')->countQuestions( $chapitre );

        return new Response('{"success":true, "childs":'.$stillHaveChilds.'}', 200);
    }

    /**
     * Affiche le formulaire d'ajout de Question.
     */
    public function addAction(Chapitre $chapitre, Request $request)
    {
        $question = $this->get('hopitalnumerique_autodiag.manager.question')->createEmpty();
        $question->setChapitre( $chapitre );

        $form = $this->createForm( 'hopitalnumerique_autodiag_question', $question);

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Question:edit.html.twig' , array(
            'form'     => $form->createView(),
            'question' => $question,
            'chapitre' => $chapitre
        ));
    }

    /**
     * Affiche le formulaire d'edition de Question.
     */
    public function editAction(Question $question, Request $request)
    {
        $form = $this->createForm( 'hopitalnumerique_autodiag_question', $question);

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Question:edit.html.twig' , array(
            'form'     => $form->createView(),
            'question' => $question,
            'chapitre' => $question->getChapitre()
        ));
    }

    /**
     * Sauvegarde AJAX de la question
     */
    public function saveAction($id, Request $request)
    {
        if( $id == 0 ){
            //cas nouvelle question, on récupère l'ID du chapitre
            $datas    = $request->request->get('hopitalnumerique_autodiag_question');
            $chapitre = $this->get('hopitalnumerique_autodiag.manager.chapitre')->findOneBy( array('id' => intval($datas['chapitre']) ) );

            if( !$chapitre )
                return new Response('<h3>Une erreur est survenue, merci de réessayé</h3>', 200);

            $question = $this->get('hopitalnumerique_autodiag.manager.question')->createEmpty();
            $question->setChapitre( $chapitre );
        }else{
            $question = $this->get('hopitalnumerique_autodiag.manager.question')->findOneBy( array('id'=>$id) );
            $chapitre = $question->getChapitre();
        }

        //build form
        $form = $this->createForm( 'hopitalnumerique_autodiag_question', $question);

        if ( $form->handleRequest($request)->isValid() ) {
            //calcul question ponderation
            $ponderation = $this->get('hopitalnumerique_autodiag.manager.question')->calculPonderationLeft($question);
            if( $ponderation !== true )
                return new Response('ponderation:'.$ponderation, 200);

            $this->get('hopitalnumerique_autodiag.manager.question')->saveQuestion( $question );

            //actualise render
            $questions = $this->get('hopitalnumerique_autodiag.manager.question')->findBy( array('chapitre' => $chapitre ) );
            return $this->render( 'HopitalNumeriqueAutodiagBundle:Question:index.html.twig' , array(
                'questions' => $questions,
                'chapitre'  => $chapitre
            ));
        }

        return new Response('<h3>Une erreur est survenue, merci de réessayé</h3>', 200);
    }










}