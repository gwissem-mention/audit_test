<?php

namespace HopitalNumerique\AutodiagBundle\Model\FileImport;

use HopitalNumerique\AutodiagBundle\Model\AutodiagFileImport;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Survey extends AutodiagFileImport
{
    /**
     * @Assert\Callback
     *
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
            $headers = $sheet->rangeToArray('A1:K1');
            $headers = $headers[0];

            $columnsCount = count(ChapterColumnsDefinition::getColumns());

            if (count($headers) !== $columnsCount
                || count(
                    array_intersect($headers, ChapterColumnsDefinition::getColumns())
                ) !== $columnsCount
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
            $headers = $sheet->rangeToArray('A1:N1');
            $headers = $headers[0];

            $columnsCount = count(AttributeColumnsDefinition::getColumns());

            if (count($headers) !== $columnsCount
                || count(array_intersect($headers, AttributeColumnsDefinition::getColumns())) !== $columnsCount
            ) {
                $context->buildViolation('ad.autodiag.import.survey.incorrect_columns.questions')
                    ->addViolation();
            }
        }
    }
}
