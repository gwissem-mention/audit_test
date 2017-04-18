<?php

namespace HopitalNumerique\QuestionnaireBundle\Enum;

/**
 * Class TemplateQuestionAlias
 *
 * Lists the aliases of the questions that have a download link in their label
 */
class TemplateQuestionAlias
{
    private static $questionAliasesWithTemplate = ['dpi'];

    /**
     * @param $alias
     *
     * @return bool
     */
    public static function hasTemplate($alias)
    {
        foreach (TemplateQuestionAlias::$questionAliasesWithTemplate as $questionAlias) {
            if ($alias == $questionAlias) {
                return true;
            }
        }

        return false;
    }
}
