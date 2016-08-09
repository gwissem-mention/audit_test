<?php

namespace HopitalNumerique\AutodiagBundle\Model\FileImport;

use HopitalNumerique\AutodiagBundle\Model\AutodiagFileImport;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Algorithm extends AutodiagFileImport
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


        $sheet = $reader->getSheetByName('algorithme');
        if (null === $sheet) {
            $context->buildViolation('ad.autodiag.import.algorithm.sheet_not_found')
                ->addViolation();
        } else {
            $headers = $sheet->rangeToArray('A1:AE1');
            $headers = $headers[0];

            if (count($headers) !== 31
                || count(array_intersect($headers, [
                    'algorithme',
                    'libelle_valeur_reference_1',
                    'calcul_valeur_reference_1',
                    'couleur_reference_1',
                    'libelle_valeur_reference_2',
                    'calcul_valeur_reference_2',
                    'couleur_reference_2',
                    'libelle_valeur_reference_3',
                    'calcul_valeur_reference_3',
                    'couleur_reference_3',
                    'libelle_valeur_reference_4',
                    'calcul_valeur_reference_4',
                    'couleur_reference_4',
                    'libelle_valeur_reference_5',
                    'calcul_valeur_reference_5',
                    'couleur_reference_5',
                    'libelle_valeur_reference_6',
                    'calcul_valeur_reference_6',
                    'couleur_reference_6',
                    'libelle_valeur_reference_7',
                    'calcul_valeur_reference_7',
                    'couleur_reference_7',
                    'libelle_valeur_reference_8',
                    'calcul_valeur_reference_8',
                    'couleur_reference_8',
                    'libelle_valeur_reference_9',
                    'calcul_valeur_reference_9',
                    'couleur_reference_9',
                    'libelle_valeur_reference_10',
                    'calcul_valeur_reference_10',
                    'couleur_reference_10',
                ])) !== 31
            ) {
                $context->buildViolation('ad.autodiag.import.algorithm.incorrect_columns')
                    ->addViolation();
            }
        }
    }
}
