<?php

namespace HopitalNumerique\QuestionnaireBundle\Enum;

/**
 * Class TemplateQuestionAlias
 *
 * Lists the aliases of the questions that have a download link in their label
 */
class TemplateQuestionAliasEnum
{
    const DPI = 'dpi';

    /**
     * @return array
     */
    public static function getQuestionsAliasesWithTemplate()
    {
        return [
            self::DPI,
        ];
    }
}
