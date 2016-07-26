<?php

namespace HopitalNumerique\AutodiagBundle\Model\FileImport;

use HopitalNumerique\AutodiagBundle\Model\AutodiagFileImport;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Survey extends AutodiagFileImport
{
    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     */
    public function validateFileContent(ExecutionContextInterface $context)
    {
        $inputFileType = \PHPExcel_IOFactory::identify($this->file);
        /** @var \PHPExcel_Reader_HTML $objReader */
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);

        try {
            /** @var \PHPExcel $reader */
            $reader = $objReader->load($this->file);
        } catch (\Exception $e) {
            $context->buildViolation('ad.autodiag.import.survey.invalid_file_type')
                ->addViolation();
            return;
        }


        $sheet = $reader->getSheetByName('chapitres');
        if (null === $sheet) {
            $context->buildViolation('ad.autodiag.import.survey.sheet_not_found.chapters')
                ->addViolation();
        } else {
            $headers = $sheet->rangeToArray('A1:H1');
            $headers = $headers[0];
            if (count($headers) !== 8
                || count(array_intersect_key($headers, [
                    'code_chapitre',
                    'code_chapitre_enfant',
                    'libelle_chapitre',
                    'libelle_chapitre_enfant',
                    'titre_avant',
                    'texte_avant',
                    'texte_apres',
                    'plan_action',
                ])) !== 8
            ) {
                $context->buildViolation('ad.autodiag.import.survey.incorrect_columns.chapters')
                    ->addViolation();
            }
        }

        $sheet = $reader->getSheetByName('questions');
        if (null === $sheet) {
            $context->buildViolation('ad.autodiag.import.survey.sheet_not_found.questions')
                ->addViolation();
        } else {
            $headers = $sheet->rangeToArray('A1:J1');
            $headers = $headers[0];
            if (count($headers) !== 10
                || count(array_intersect_key($headers, [
                    "code_question",
                    "code_chapitre",
                    "texte_avant",
                    "libelle_question",
                    "format_reponse",
                    "items_reponse",
                    "colorer_reponse",
                    "infobulle_question",
                    "ponderation_categorie",
                    "ponderation_chapitre",
                ])) !== 10
            ) {
                $context->buildViolation('ad.autodiag.import.survey.incorrect_columns.questions')
                    ->addViolation();
            }
        }
    }
}
