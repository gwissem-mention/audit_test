<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Risk\Export;

use HopitalNumerique\ObjetBundle\Entity\RelatedRisk;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;

class RiskExcelExport extends RiskExport
{

    /**
     * @inheritdoc
     */
    public function exportGuidedSearchStepRisks(GuidedSearchStep $guidedSearchStep, $risks)
    {
        $phpexcel = new \PHPExcel();
        $letters = range('A', 'Z');

        $sheet = $phpexcel
            ->getActiveSheet()
            ->setTitle('Export')
        ;

        $headers = array_merge($this->getHeader(), [
            $this->translator->trans('step.risks.header.action', [], 'guided_search'),
            $this->translator->trans('step.risks.header.actor', [], 'guided_search'),
            $this->translator->trans('step.risks.header.deadline', [], 'guided_search'),
            $this->translator->trans('step.risks.header.state', [], 'guided_search'),
        ]);
        foreach ($headers as $k => $header) {
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

        $this->reorderRisks($risks);

        $criticalityConditions = $this->createCriticalityConditions();

        foreach ($risks as $risk) {
            $sheet->setCellValue(sprintf('A%d', $line), $risk->natureLabel);
            $sheet->setCellValue(sprintf('B%d', $line), $risk->label);
            $sheet->setCellValue(sprintf('C%d', $line), $risk->probability);
            $sheet->setCellValue(sprintf('D%d', $line), $risk->impact);
            $sheet->setCellValue(sprintf('E%d', $line), sprintf('=IF(SUM(C%d:D%d) > 1, PRODUCT(C%d:D%d), "")', $line, $line, $line, $line));
            $sheet->getStyle(sprintf('E%d', $line))->setConditionalStyles($criticalityConditions);
            $sheet->setCellValue(sprintf('F%d', $line), $risk->initialSkillsRate);
            $sheet->setCellValue(sprintf('G%d', $line), $risk->currentSkillsRate);
            $sheet->setCellValue(sprintf('H%d', $line), implode("\n", $this->getDisplayableResources($risk)));
            $sheet->setCellValue(sprintf('I%d', $line), $risk->comment);

            $sheet->getStyle(sprintf('I%d', $line))->getAlignment()->setWrapText(true);
            $sheet->getStyle(sprintf('H%d', $line))->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($line)->setRowHeight(-1);

            $line++;
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
        $objWriter->setPreCalculateFormulas();
        $filepath = $this->getFilePath();
        $objWriter->save($filepath);

        return $filepath;
    }

    /**
     * @return array
     */
    private function createCriticalityConditions()
    {
        $criticalityCondition1 = new \PHPExcel_Style_Conditional();
        $criticalityCondition1->setConditionType(\PHPExcel_Style_Conditional::CONDITION_CELLIS)
            ->setOperatorType(\PHPExcel_Style_Conditional::OPERATOR_BETWEEN)
            ->addCondition(0)
            ->addCondition(2)
            ->getStyle()
            ->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getEndColor()->setRGB('2ecc71')
        ;

        $criticalityCondition2 = new \PHPExcel_Style_Conditional();
        $criticalityCondition2->setConditionType(\PHPExcel_Style_Conditional::CONDITION_CELLIS)
            ->setOperatorType(\PHPExcel_Style_Conditional::OPERATOR_BETWEEN)
            ->addCondition(3)
            ->addCondition(7)
            ->getStyle()
            ->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getEndColor()->setRGB('f1c40f')
        ;

        $criticalityCondition3 = new \PHPExcel_Style_Conditional();
        $criticalityCondition3->setConditionType(\PHPExcel_Style_Conditional::CONDITION_CELLIS)
            ->setOperatorType(\PHPExcel_Style_Conditional::OPERATOR_BETWEEN)
            ->addCondition(8)
            ->addCondition(11)
            ->getStyle()
            ->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getEndColor()->setRGB('e67e22')
        ;

        $criticalityCondition4 = new \PHPExcel_Style_Conditional();
        $criticalityCondition4->setConditionType(\PHPExcel_Style_Conditional::CONDITION_CELLIS)
            ->setOperatorType(\PHPExcel_Style_Conditional::OPERATOR_BETWEEN)
            ->addCondition(12)
            ->addCondition(16)
            ->getStyle()
            ->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getEndColor()->setRGB('e74c3c')
        ;

        return [
            $criticalityCondition4,
            $criticalityCondition3,
            $criticalityCondition2,
            $criticalityCondition1,
        ];
    }
}
