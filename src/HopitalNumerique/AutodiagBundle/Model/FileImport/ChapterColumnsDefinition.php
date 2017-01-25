<?php

namespace HopitalNumerique\AutodiagBundle\Model\FileImport;

final class ChapterColumnsDefinition
{
    const CODE = 'code_chapitre';
    const ORDER = 'ordre_chapitre';
    const NUMBER = 'numero_chapitre';
    const LABEL = 'libelle_chapitre';
    const CHILD_CODE = 'code_chapitre_enfant';
    const CHILD_NUMBER = 'numero_chapitre_enfant';
    const CHILD_LABEL = 'libelle_chapitre_enfant';
    const TITLE = 'titre_avant';
    const DESCRIPTION = 'texte_avant';
    const ADDITIONAL_DESCRIPTION = 'texte_apres';
    const ACTION_PLAN = 'plan_action';

    /**
     * Get columns for import and export
     *
     * @return array
     */
    public static function getColumns()
    {
        return [
            self::CODE,
            self::ORDER,
            self::NUMBER,
            self::LABEL,
            self::CHILD_CODE,
            self::CHILD_NUMBER,
            self::CHILD_LABEL,
            self::TITLE,
            self::DESCRIPTION,
            self::ADDITIONAL_DESCRIPTION,
            self::ACTION_PLAN,
        ];
    }
}
