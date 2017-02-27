<?php

namespace HopitalNumerique\QuestionnaireBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;

/**
 * Manager de l'entité Contractualisation.
 */
class QuestionManager extends BaseManager
{
    protected $class = 'HopitalNumerique\QuestionnaireBundle\Entity\Question';

    /**
     * Recupère toutes les questions du type passé en paramètre pour le questionnaire $idQuestionnaire.
     *
     * @param int    $idQuestionnaire
     * @param string $typeQuestion
     *
     * @return array(\HopitalNumerique\QuestionnaireBundle\Entity\Questions) Tableau des questions de type $typeQuestion
     */
    public function getQuestionsByType($idQuestionnaire, $typeQuestion)
    {
        return $this->getRepository()->getQuestionsByType($idQuestionnaire, $typeQuestion);
    }

    /**
     * Retourne la path de l'endroit où on doit upload un fichier.
     *
     * @param string $questionnaire
     *
     * @return string Chemin root du fichier à uploader
     */
    public function getUploadRootDir($labelQuestionnaire)
    {
        if (!file_exists(__ROOT_DIRECTORY__ . '/files/' . $labelQuestionnaire)) {
            return null;
        }

        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __ROOT_DIRECTORY__ . '/files/' . $labelQuestionnaire;
    }

    /**
     * Retourne une question du questionnaire par rapport à son ID.
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire Le questionnaire sur lequel récupérer la question
     * @param int                                                        $questionId    L'ID de la question à récupérer
     *
     * @return \HopitalNumerique\QuestionnaireBundle\Entity\Question|null La question de l'ID ou NIL si non trouvé
     */
    public function getQuestionById(Questionnaire $questionnaire, $questionId)
    {
        foreach ($questionnaire->getQuestions() as $question) {
            if ($questionId == $question->getId()) {
                return $question;
            }
        }

        return null;
    }

    /**
     * Récupère le nombre de question pour un questionnaire donné en parametre.
     *
     * @param [int] $idQuestionnaire Identifiant du questionnaire
     *
     * @return int Nombre de questions
     */
    public function countQuestions($idQuestionnaire)
    {
        return $this->getRepository()->countQuestions($idQuestionnaire)->getQuery()->getSingleScalarResult();
    }

    /**
     * Met à jour l'ordre des questions.
     *
     * @param array  $elements Les éléments
     * @param object $parent   L'élément parent | null
     *
     * @return empty
     */
    public function reorder($elements)
    {
        $order = 1;

        foreach ($elements as $element) {
            $question = $this->findOneBy(['id' => $element['id']]);
            $question->setOrdre($order);
            ++$order;
        }
    }
}
