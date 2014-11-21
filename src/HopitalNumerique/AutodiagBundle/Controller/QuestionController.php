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
        //get ponderations
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();

        //get questions
        $questions = $this->get('hopitalnumerique_autodiag.manager.question')->getQuestionsOrdered( $chapitre, $refsPonderees );

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
     * Edit le lien d'une question.
     */
    public function editLienAction( Question $question )
    {
        $texte = $this->get('request')->request->get('texte');
        $question->setLien($texte);
        
        //delete
        $this->get('hopitalnumerique_autodiag.manager.question')->save( $question );

        return new Response('{"success":true}', 200);
    }

    /**
     * Recupère le lien d'une question.
     */
    public function getLienAction( $id )
    {
        $question = $this->get('hopitalnumerique_autodiag.manager.question')->findOneBy( array('id' => $id) );
        $lien = is_null($question->getLien()) ? '' : $question->getLien();

        return new Response('{"success":true, "lien":"'. $lien .'"}', 200);
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

            //Parse les options de réponses : Force un point si jamais il y a une virgule dans la value
            $options = explode("\n", $question->getOptions());
            foreach ($options as &$option) 
            {
                $values = explode(";", $option);
                $values[0] = str_replace(",", ".", $values[0]);
                $option = implode(";", $values);
            }
            $question->setOptions(implode("\n", $options));

            //Sauvegarde
            $this->get('hopitalnumerique_autodiag.manager.question')->saveQuestion( $question );

            //get ponderations
            $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();

            //get questions
            $questions = $this->get('hopitalnumerique_autodiag.manager.question')->getQuestionsOrdered( $chapitre, $refsPonderees );

            return $this->render( 'HopitalNumeriqueAutodiagBundle:Question:index.html.twig' , array(
                'questions' => $questions,
                'chapitre'  => $chapitre
            ));
        }

        return new Response('<h3>Une erreur est survenue, merci de réessayé</h3>', 200);
    }

    /**
     * Met à jour l'ordre des différentes questions
     */
    public function reorderAction(Chapitre $chapitre)
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_autodiag.manager.question')->reorder( $datas );
        $this->getDoctrine()->getManager()->flush();

        //return success.true si le fichier existe deja
        return new Response('{"success":true}', 200);
    }
}
