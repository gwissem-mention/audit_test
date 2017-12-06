<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller\Back;

use HopitalNumerique\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\QuestionnaireBundle\Entity\Occurrence;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;

class SurveyController extends Controller
{
    /**
     * @param Questionnaire $survey
     * @param User $user
     * @param Occurrence|null $entry
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ParamConverter("entry", class="HopitalNumeriqueQuestionnaireBundle:Occurrence", options={"id" = "entry"})
     */
    public function showAction(Questionnaire $survey, User $user, Occurrence $entry = null)
    {
        $form = $this->createForm('nodevo_questionnaire_questionnaire', $survey, [
            'disabled' => true,
            'label_attr' => [
                'idUser' => $user->getId(),
                'idQuestionnaire' => $survey->getId(),
                'occurrence' => $entry,
                'routeRedirection' => null,
                'readOnly' => true,
                'idSession' => null,
                'paramId' => null,
            ],
        ]);

        return $this->render('@HopitalNumeriqueQuestionnaire/back/survey/show.html.twig', [
            'survey' => $survey,
            'form' => $form->createView(),
        ]);
    }
}
