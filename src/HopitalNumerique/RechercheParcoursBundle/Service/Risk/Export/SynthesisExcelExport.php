<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Risk\Export;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;

class SynthesisExcelExport extends SynthesisExport
{

    /**
     * @inheritdoc
     */
    public function exportGuidedSearch(GuidedSearch $guidedSearch, User $user = null)
    {
        $phpexcel = new \PHPExcel();
        $letters = range('A', 'Z');

        $sheet = $phpexcel
            ->getActiveSheet()
            ->setTitle('Export')
        ;

        foreach ($this->getHeader() as $k => $header) {
            $sheet->setCellValue(sprintf('%s1', $letters[$k]), $header);
            $sheet->getColumnDimension($letters[$k])->setAutoSize(true);
            $sheet->getStyle(sprintf('%s1', $letters[$k]))->applyFromArray(
                [
                    'fill' => [
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['rgb' => 'bdc3c7'],
                    ],
                    'borders' => [
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '7f8c8d'],
                        ],
                    ],
                ]
            );
        }

        $line = 2;

        foreach ($this->parseGuidedSearch($guidedSearch, $user) as $risk) {
            foreach ($risk as $k => $field) {
                $sheet->setCellValue(sprintf('%s%d', range('A', 'Z')[$k], $line), $field);
            }

            $line++;
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
        $objWriter->save('php://output');
    }
}
