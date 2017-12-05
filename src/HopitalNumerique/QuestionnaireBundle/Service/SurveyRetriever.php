<?php

namespace HopitalNumerique\QuestionnaireBundle\Service;

use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use HopitalNumerique\QuestionnaireBundle\Repository\ReponseRepository;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class SurveyRetriever
 *
 * Get user survey grouped by survey if multi-occurence survey
 */
class SurveyRetriever
{
    /**
     * @var ReponseRepository
     */
    protected $responseRepository;

    /**
     * SurveyRetriever constructor.
     *
     * @param ReponseRepository $responseRepository
     */
    public function __construct(ReponseRepository $responseRepository)
    {
        $this->responseRepository = $responseRepository;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getUserResponsesGroupedBySurvey(User $user)
    {
        $responses = $this->responseRepository->findByUserOrderedBySurveyNameAndResponseUpdate($user);

        $data = [];

        /** @var Reponse $response */
        foreach ($responses as $response) {
            $entry = $response->getOccurrence();
            $question = $response->getQuestion();
            $survey = $question->getQuestionnaire();

            if (!isset($data[$survey->getId()])) {
                $data[$survey->getId()] = [
                    'survey' => [
                        'id' => $survey->getId(),
                        'name' => $survey->getNom(),
                        'update' => $response->getDateUpdate(),
                        'actions' => [],
                    ],
                    'responses' => [],
                ];
            }

            if (null !== $entry && !isset($data[$survey->getId()['responses'][$entry->getId()]])) {
                $data[$survey->getId()]['responses'][$entry->getId()] = [
                    'id' => $entry->getId(),
                    'name' => $entry->getLibelle(),
                    'update' => $response->getDateUpdate(),
                ];
            }
        }

        return $data;
    }
}
