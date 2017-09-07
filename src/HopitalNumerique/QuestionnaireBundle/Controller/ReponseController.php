<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller;

use HopitalNumerique\QuestionnaireBundle\Entity\Occurrence;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Entity\User as HopiUser;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReponseController extends Controller
{
    /**
     * Téléchargement des fichiers attaché au questionnaire expert.
     *
     * @param Reponse $reponse
     *
     * @return RedirectResponse|Response
     */
    public function dowloadReponseAction(Reponse $reponse)
    {
        if (file_exists(
            $this->get('hopitalnumerique_questionnaire.manager.question')->getUploadRootDir(
                $reponse->getQuestion()->getQuestionnaire()->getNomMinifie()
            ) . '/' . $reponse->getReponse()
        )) {
            $option = $this->get('hopitalnumerique_questionnaire.manager.reponse')->download($reponse);

            $file = new File(
                $this->get('hopitalnumerique_questionnaire.manager.question')->getUploadRootDir(
                    $reponse->getQuestion()->getQuestionnaire()->getNomMinifie()
                ) . '/' . $reponse->getReponse()
            );

            return $this->get('igorw_file_serve.response_factory')->create(
                $file->getPathname(),
                $file->getMimeType(),
                $option
            );
        } else {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas :
            // suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add(('danger'), 'Le document n\'existe plus sur le serveur.');

            //Si l'url courante contient le mot clé "admin"
            if (strpos($this->get('request')->getPathInfo(), 'admin') !== false) {
                //BackOffice
                return $this->redirect($this->generateUrl('hopital_numerique_user_homepage'));
            } else {
                //FrontOffice
                return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
            }
        }
    }

    /**
     * Suppression de toutes les réponses de l'utilisateur pour le questionnaire passé en param.
     *
     * @param HopiUser      $user
     * @param Questionnaire $questionnaire
     *
     * @return Response
     */
    public function deleteAllAction(HopiUser $user, Questionnaire $questionnaire)
    {
        $routeRedirection = $this->get('request')->request->get('routeRedirection');
        $routeRedirection = json_decode($routeRedirection, true);

        $this->get('hopitalnumerique_questionnaire.manager.reponse')->deleteAll(
            $user->getId(),
            $questionnaire->getId()
        );

        $this->get('session')->getFlashBag()->add(
            'success',
            'Le questionnaire ' . $questionnaire->getNomMinifie() . ' a été vidé.'
        );

        return new Response(
            '{"success":true, "url" : "' . $this->generateUrl(
                $routeRedirection['sauvegarde']['route'],
                $routeRedirection['sauvegarde']['arguments']
            ) . '"}',
            200
        );
    }

    /**
     * Deletes responses from current user
     *
     * @param Request         $request
     * @param Questionnaire   $survey
     * @param Occurrence|null $entry
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Questionnaire $survey, Occurrence $entry = null)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        if (null === $entry) {
            $toDelete = $this->get('hopitalnumerique_questionnaire.repository.response')->findByUserAndSurvey(
                $this->getUser(),
                $survey
            );
        } else {
            $toDelete = $this->get('hopitalnumerique_questionnaire.repository.response')->findByUserAndSurveyAndEntry(
                $this->getUser(),
                $survey,
                $entry
            );

            $toDelete[] = $entry;
        }

        try {
            foreach ($toDelete as $item) {
                $em->remove($item);
            }

            $em->flush();
        } catch (\Exception $exception) {
            $this->addFlash('danger', 'Une erreur s\'est produite lors de la suppression de vos réponses');

            return $this->redirect($request->headers->get('referer'));
        }

        $this->addFlash('success', 'Vos réponses ont bien été supprimées');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Suppression de toutes les réponses de tout les utilisateurs pour le questionnaire passé en param.
     *
     * @param Questionnaire $questionnaire
     *
     * @return Response
     */
    public function deleteAllByQuestionnaireAction(Questionnaire $questionnaire)
    {
        //Suppression des entitées
        $this->get('hopitalnumerique_questionnaire.manager.reponse')->deleteAllByQuestionnaire($questionnaire->getId());

        $this->get('session')->getFlashBag()->add(
            'success',
            'Le questionnaire ' . $questionnaire->getNomMinifie() . ' a été vidé de ses réponses.'
        );

        return new Response(
            '{"success":true, "url" : "' . $this->generateUrl('hopitalnumerique_questionnaire_index') . '"}',
            200
        );
    }

    /**
     * Suppression de masse des réponses des questionnaires sélectionnés.
     *
     * @param array $primaryKeys
     * @param array $allPrimaryKeys
     *
     * @return RedirectResponse
     */
    public function deleteMassAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        foreach ($primaryKeys as $idQuestionnaire) {
            $this->get('hopitalnumerique_questionnaire.manager.reponse')->deleteAllByQuestionnaire($idQuestionnaire);
        }

        $this->get('session')->getFlashBag()->add('info', 'Suppression des réponses effectuée avec succès.');

        return $this->redirect($this->generateUrl('hopitalnumerique_questionnaire_index'));
    }
}
