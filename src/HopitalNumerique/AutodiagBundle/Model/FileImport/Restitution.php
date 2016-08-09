<?php

namespace HopitalNumerique\AutodiagBundle\Model\FileImport;

use HopitalNumerique\AutodiagBundle\Model\AutodiagFileImport;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Restitution extends AutodiagFileImport
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


        $sheet = $reader->getActiveSheet();
        $headers = $sheet->rangeToArray('A1:R1');
        $headers = $headers[0];

        if (count($headers) !== 18
            || count(array_intersect($headers, [
                'libelle_onglet',
                'ordre_affichage_onglet',
                'ordre_affichage_contenu',
                'texte_avant',
                'type_restitution',
                'axe_restitution',
                'donnees',
                'ordre_restitution',
                'afficher_reference_1',
                'afficher_reference_2',
                'afficher_reference_3',
                'afficher_reference_4',
                'afficher_reference_5',
                'afficher_reference_6',
                'afficher_reference_7',
                'afficher_reference_8',
                'afficher_reference_9',
                'afficher_reference_10',
            ])) !== 18
        ) {
            $context->buildViolation('ad.autodiag.import.restitution.incorrect_columns')
                ->addViolation();
        }

    }
}
