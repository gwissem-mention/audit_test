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
     * Sauvegarde AJAX de la question
     */
    public function saveAction($id, Request $request)
    {
        if( $id == 0 ){
            //cas nouvelle question, on récupère l'ID du chapitre
            $datas    = $request->request->get('hopitalnumerique_autodiag_question');
            $chapitre = $this->get('hopitalnumerique_autodiag.manager.chapitre')->findOneBy( array('id' => intval($datas['chapitre']) ) );

            if( ! $chapitre )
                return new Response('{"success":false}', 200);

            $question = $this->get('hopitalnumerique_autodiag.manager.question')->createEmpty();
            $question->setChapitre( $chapitre );
        }else
            $question = $this->get('hopitalnumerique_autodiag.manager.question')->findOneBy( array('id'=>$id) );

        //build form
        $form = $this->createForm( 'hopitalnumerique_autodiag_question', $question);

        if ( $form->handleRequest($request)->isValid() ) {
            
            $this->get('hopitalnumerique_autodiag.manager.question')->saveQuestion( $question );

            
            return new Response('{"success":true}', 200);
        }

        return new Response('{"success":false}', 200);
    }










}