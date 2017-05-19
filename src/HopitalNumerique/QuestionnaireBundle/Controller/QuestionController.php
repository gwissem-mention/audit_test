<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller;

use HopitalNumerique\QuestionnaireBundle\Entity\Question;
use HopitalNumerique\QuestionnaireBundle\Entity\TypeQuestion;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\QuestionnaireBundle\Entity\Question as HopiQuestion;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller des Questionnaire.
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class QuestionController extends Controller
{
    /**
     * @param $id
     *
     * @return Response
     */
    public function indexAction($id)
    {
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy(['id' => $id]);

        return $this->render('HopitalNumeriqueQuestionnaireBundle:Question:index.html.twig', [
                'questionnaire' => $questionnaire,
            ]);
    }

    /**
     * Ajoute une question.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addQuestionAction(Request $request)
    {
        $typeQuestions = $this->get('hopitalnumerique_questionnaire.manager.typequestion')->findOneBy(
            [],
            ['nom' => 'ASC']
        );

        $typeQuestionDefault = $this->get('hopitalnumerique_questionnaire.manager.typequestion')->findOneBy(
            ['id' => 1]
        );

        /** @var Question $question */
        $question = $this->get('hopitalnumerique_questionnaire.manager.question')->createEmpty();
        $idQuestionnaire = trim($request->request->get('idQuestionnaire'));

        // Calcul de l'ordre
        $order = $this->get('hopitalnumerique_questionnaire.manager.question')->countQuestions($idQuestionnaire) + 1;
        $titre = trim($request->request->get('titre')) ?: 'Question ' . $order;
        $tool = new Chaine($titre);

        $question->setOrdre($order);
        $question->setLibelle($titre);
        $question->setAlias($tool->minifie());
        $question->setObligatoire(false);
        $question->setQuestionnaire(
            $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy(['id' => $idQuestionnaire])
        );
        $question->setTypeQuestion($typeQuestionDefault);

        $this->get('hopitalnumerique_questionnaire.manager.question')->save($question);

        return $this->render('HopitalNumeriqueQuestionnaireBundle:Question:add.html.twig', [
            'question' => $question,
            'typeQuestions' => $typeQuestions,
        ]);
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function editViewAction($id)
    {
        /** @var Question $question */
        $question = $this->get('hopitalnumerique_questionnaire.manager.question')->findOneBy(['id' => $id]);
        /** @var TypeQuestion[] $typeQuestions */
        $typeQuestions = $this->get('hopitalnumerique_questionnaire.manager.typequestion')->findBy(
            [],
            ['nom' => 'ASC']
        );
        /** @var Reference[] $references */
        $references = $this->get('hopitalnumerique_reference.manager.reference')->getAllRefCode(
            $question->getQuestionnaire()
        );

        return $this->render('HopitalNumeriqueQuestionnaireBundle:Question:edit.html.twig', [
                'question' => $question,
                'typeQuestions' => $typeQuestions,
                'references' => $references,
            ]);
    }

    /**
     * Met à jour l'ordre des différentes questions.
     *
     * @return Response
     */
    public function reorderAction()
    {
        // Get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        // Execute reorder
        $this->get('hopitalnumerique_questionnaire.manager.question')->reorder($datas);
        $this->getDoctrine()->getManager()->flush();

        return new Response('{"success":true}', 200);
    }

    /**
     * Edite une question.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function saveAction(Request $request)
    {
        //Récupération des ids passés en data
        $idQuestion = $request->request->get('id');
        $idTypeQuestion = $request->request->get('typeQuestion');
        $commentaire = $request->request->get('commentaire_question');
        $obligatoire = $request->request->get('obligatoire') === 'true' ? true : false;

        /** @var Question $question */
        $question = $this->get('hopitalnumerique_questionnaire.manager.question')->findOneBy(['id' => $idQuestion]);

        /** @var TypeQuestion $typeQuestion */
        $typeQuestion = $this->get('hopitalnumerique_questionnaire.manager.typequestion')->findOneBy(
            ['id' => $idTypeQuestion]
        );

        $verifJS = '';

        if ($typeQuestion->getId() == 1) {
            $verifJS = $obligatoire ? 'validate[required,minSize[1],maxSize[255]]' : 'validate[maxSize[255]]';
        } elseif ($typeQuestion->getId() == 13) {
            $question->setCommentaire($commentaire);
        } else {
            $verifJS = $obligatoire ? 'validate[required]' : '';
        }

        $question->setLibelle(trim($request->request->get('libelle')) ?: 'Question ' . $question->getId());
        $question->setObligatoire($obligatoire);
        $question->setVerifJS($verifJS);
        $question->setTypeQuestion($typeQuestion);
        $question->setReferenceParamTri($request->request->get('refTypeQuestion'));

        $this->get('hopitalnumerique_questionnaire.manager.question')->save($question);

        $this->addFlash('success', 'Question ' . $question->getLibelle() . ' mise à jour.');

        return new Response('{"success":true}', 200);
    }

    /**
     * Suppresion d'un chapitre.
     * METHOD = POST|DELETE.
     *
     * @param HopiQuestion $question
     *
     * @return Response
     */
    public function deleteAction(HopiQuestion $question)
    {
        $this->get('hopitalnumerique_questionnaire.manager.question')->delete($question);

        return new Response('{"success":true}', 200);
    }

    /**
     * @param Question $question
     *
     * @return Response
     */
    public function downloadTemplateFileAction(Question $question)
    {
        $filename = sprintf('template_%s.docx', $question->getAlias());
        $path = $this->get('kernel')->getRootDir(). '/../files/expert/'.$filename;

        if (!$question->hasTemplate() || !file_exists($path)) {
            throw new NotFoundHttpException('Le modèle demandé n\'existe pas.');
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        return $response;
    }
}
