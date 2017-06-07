<?php

namespace HopitalNumerique\AutodiagBundle\Model\FileImport;

final class AttributeColumnsDefinition
{
    const CODE = 'code_question';
    const ORDER = 'ordre_question';
    const CHAPTER = 'code_chapitre';
    const DESCRIPTION = 'texte_avant';
    const NUMBER = 'numero_question';
    const LABEL = 'libelle_question';
    const ADDITIONAL_DESCRIPTION = 'texte_apres';
    const UNIT = 'unite_reponse';
    const TYPE = 'format_reponse';
    const OPTIONS = 'items_reponse';
    const COLORED = 'colorer_reponse';
    const TOOLTIP = 'infobulle_question';
    const CATEGORY_WEIGHT = 'ponderation_categories';
    const CHAPTER_WEIGHT = 'ponderation_chapitre';

    /**
     * Get columns for import and export.
     *
     * @return array
     */
    public static function getColumns()
    {
        return [
            self::CODE,
            self::ORDER,
            self::CHAPTER,
            self::DESCRIPTION,
            self::NUMBER,
            self::LABEL,
            self::ADDITIONAL_DESCRIPTION,
            self::UNIT,
            self::TYPE,
            self::OPTIONS,
            self::COLORED,
            self::TOOLTIP,
            self::CATEGORY_WEIGHT,
            self::CHAPTER_WEIGHT,
        ];
    }
}
