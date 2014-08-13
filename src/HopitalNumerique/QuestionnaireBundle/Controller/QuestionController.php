<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use HopitalNumerique\QuestionnaireBundle\Entity\Question as HopiQuestion;

use Nodevo\ToolsBundle\Tools\Chaine;

/**
 * Controller des Questionnaire
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class QuestionController extends Controller
{
    public function indexAction($id)
    {
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy(array('id' => $id));

        return $this->render('HopitalNumeriqueQuestionnaireBundle:Question:index.html.twig', array(
                'questionnaire' => $questionnaire
            ));
    }

    /**
     * Ajoute une question
     */
    public function addQuestionAction(Request $request)
    {
        $typeQuestions = $this->get('hopitalnumerique_questionnaire.manager.typequestion')->findOneBy(array(), array('nom' => 'ASC'));
        $typeQuestionDefault = $this->get('hopitalnumerique_questionnaire.manager.typequestion')->findOneBy(array('id' => 1));

        //créer un question
        $question        = $this->get('hopitalnumerique_questionnaire.manager.question')->createEmpty();
        $idQuestionnaire = trim($request->request->get('idQuestionnaire'));

        //Calcul de l'ordre
        $order = $this->get('hopitalnumerique_questionnaire.manager.question')->countQuestions($idQuestionnaire) + 1;
        $titre = trim($request->request->get('titre')) ? : 'Question '.$order;
        $tool  = new Chaine( $titre );

        $question->setOrdre( $order );
        $question->setLibelle( $titre );
        $question->setAlias( $tool->minifie() );
        $question->setObligatoire( false );
        $question->setQuestionnaire( $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy(array('id' => $idQuestionnaire)) );
        $question->setTypeQuestion($typeQuestionDefault);

        //save
        $this->get('hopitalnumerique_questionnaire.manager.question')->save( $question );

        return $this->render('HopitalNumeriqueQuestionnaireBundle:Question:add.html.twig', array(
            'question' => $question,
            'typeQuestions' => $typeQuestions
        ));
    }

    public function editViewAction($id)
    {
        $question      = $this->get('hopitalnumerique_questionnaire.manager.question')->findOneBy(array('id' => $id));
        $typeQuestions = $this->get('hopitalnumerique_questionnaire.manager.typequestion')->findBy(array(), array('nom' => 'ASC'));
        $references    = $this->get('hopitalnumerique_reference.manager.reference')->getAllRefCode();

        return $this->render('HopitalNumeriqueQuestionnaireBundle:Question:edit.html.twig', array(
                'question'      => $question,
                'typeQuestions' => $typeQuestions,
                'references'    => $references
            ));
    }

    /**
     * Met à jour l'ordre des différentes questions
     */
    public function reorderAction()
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_questionnaire.manager.question')->reorder( $datas );
        $this->getDoctrine()->getManager()->flush();

        return new Response('{"success":true}', 200);
    }

    /**
     * Edite une question
     */
    public function saveAction(Request $request)
    {
        //Récupération des ids passés en data
        $idQuestion     = $request->request->get('id');
        $idTypeQuestion = $request->request->get('typeQuestion');
        $obligatoire    = $request->request->get('obligatoire') === "true" ? true : false;

        //Récupère la question
        $question     = $this->get('hopitalnumerique_questionnaire.manager.question')->findOneBy(array('id' => $idQuestion));
        //Récupère le type de la question sélectionné
        $typeQuestion = $this->get('hopitalnumerique_questionnaire.manager.typequestion')->findOneBy(array('id' => $idTypeQuestion));

        $verifJS = '';
        if($typeQuestion->getId() == 1)
        {
            $verifJS = $obligatoire ? 'validate[required,minSize[1],maxSize[255]]' : 'validate[maxSize[255]]';
        }
        else
        {
            $verifJS = $obligatoire ? 'validate[required]' : '';
        }

        $question->setLibelle( trim($request->request->get('libelle')) ? : 'Question '.$question->getId() );
        $question->setObligatoire( $obligatoire );
        $question->setVerifJS( $verifJS );
        $question->setTypeQuestion( $typeQuestion );
        $question->setReferenceParamTri( $request->request->get('refTypeQuestion') );

        //save
        $this->get('hopitalnumerique_questionnaire.manager.question')->save( $question );

        // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
        $this->get('session')->getFlashBag()->add( 'success' , 'Question ' . $question->getLibelle() . ' mise à jour.'); 

        return new Response('{"success":true}', 200);
    }

    /**
     * Suppresion d'un chapitre.
     *
     * METHOD = POST|DELETE
     */
    public function deleteAction( HopiQuestion $question )
    {
        //delete
        $this->get('hopitalnumerique_questionnaire.manager.question')->delete( $question );

        return new Response('{"success":true}', 200);
    }
}