<?php
namespace HopitalNumerique\AutodiagBundle\Service\Export;

use Doctrine\Common\Persistence\ObjectManager;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Model\Result\Item;
use HopitalNumerique\AutodiagBundle\Service\Result\ResultItemBuilder;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\Completion;

class AutodiagEntriesExport extends AbstractExport
{
    /**
     * @var ResultItemBuilder
     */
    protected $resultItemBuilder;

    /**
     * @var Completion
     */
    protected $completion;

    public function __construct(ObjectManager $manager, ResultItemBuilder $resultItemBuilder, Completion $completion)
    {
        parent::__construct($manager);

        $this->resultItemBuilder = $resultItemBuilder;
        $this->completion = $completion;
    }

    /**
     * @param Synthesis[] $syntheses
     */
    public function exportList($syntheses)
    {
        if (!is_array($syntheses) && count($syntheses) === 0) {
            throw new \Exception('Syntheses must be an array of Synthesis');
        }

        $autodiag = current($syntheses);
        $autodiag = $autodiag->getAutodiag();

        $excel = new \PHPExcel();
        $sheet = $excel->getActiveSheet();

        $this->writeHeader($sheet);

        $column = 'E';
        foreach ($syntheses as $synthesis) {
            $this->writeSynthesisRows($synthesis, $sheet, $column);
            $column = $this->incrementColumn($column, 2);
        }

        $this->applyStyle($sheet, $column);

        return $this->getFileResponse($excel, $autodiag->getTitle(), 'resultat');

    }

    protected function writeHeader(\PHPExcel_Worksheet $sheet)
    {
        $cells = [
            'Nom de l\'utilisateur',
            'Établissement',
            'Date de création',
            'Dernier enregistrement',
            'Date de validation',
            'Pourcentage de remplissage',
        ];

        foreach ($cells as $cell) {
            $this->addRow($sheet, [
                '',
                '',
                $cell,
            ]);
        }

        $this->addRow($sheet, [
            'Chapitre',
            'Sous-chapitre',
            'Question',
            'Pondération',
        ]);
    }

    protected function writeSynthesisRows(Synthesis $synthesis, \PHPExcel_Worksheet $sheet, $column)
    {
        $row = 8;
        foreach ($synthesis->getAutodiag()->getChapters() as $chapter) {
            $item = $this->resultItemBuilder->build($chapter, $synthesis);

            $this->writeUserRelativeData($synthesis, $column, $sheet);

            $this->writeSynthesisItem($item, $sheet, $column, $row);
        }
    }

    protected function writeSynthesisItem(Item $item, \PHPExcel_Worksheet $sheet, $column, &$row, Item $parent = null)
    {
        foreach ($item->getAttributes() as $attribute) {

            $sheet->setCellValue(sprintf('%s%s', 'A', $row), $parent ? $parent->getLabel() : $item->getLabel());
            $sheet->setCellValue(sprintf('%s%s', 'B', $row), $parent ? $item->getLabel() : '');
            $sheet->setCellValue(sprintf('%s%s', 'C', $row), $attribute->label);
            $sheet->setCellValue(sprintf('%s%s', 'D', $row), 'p');

            $sheet->setCellValue(sprintf('%s%s', $column, $row), $attribute->responseText);
            $sheet->setCellValue(sprintf('%s%s', $this->incrementColumn($column), $row), $attribute->responseValue);

            $row++;
        }


        foreach ($item->getChildrens() as $child) {
            $this->writeSynthesisItem($child, $sheet, $column, $row, $item);
        }
    }

    protected function incrementColumn($column, $nb = 1)
    {
        $nb--;
        $column++;

        if ($nb > 0) {
            $column = $this->incrementColumn($column, $nb);
        }

        return $column;
    }

    protected function writeUserRelativeData(Synthesis $synthesis, $column, \PHPExcel_Worksheet $sheet)
    {
        if ($synthesis->getUser()) {
            $sheet->setCellValue(
                sprintf('%s%s', $column, 1),
                sprintf('%s %s', $synthesis->getUser()->getPrenom(), $synthesis->getUser()->getPrenom())
            );
        } else {
            $sheet->setCellValue(sprintf('%s%s', $column, 1), 'Anonyme');
        }

        if ($synthesis->getUser()) {
            $etab = $synthesis->getUser()->getEtablissementRattachementSante();
            if (null === $etab) {
                $etab = $synthesis->getUser()->getAutreStructureRattachementSante();
            }

            if (null !== $etab) {
                $sheet->setCellValue(
                    sprintf('%s%s', $column, 2),
                    $etab->getNom()
                );
            }
        }

        $sheet->setCellValue(sprintf('%s%s', $column, 3), 'création');
        $sheet->setCellValue(sprintf('%s%s', $column, 4), $synthesis->getUpdatedAt()->format('d/m/Y'));

        $sheet->setCellValue(
            sprintf('%s%s', $column, 5),
            $synthesis->getValidatedAt() ? $synthesis->getValidatedAt()->format('d/m/Y') : ''
        );

        $sheet->setCellValue(
            sprintf('%s%s', $column, 6),
            sprintf('%s%%', $this->completion->getCompletionRate($synthesis))
        );
    }

    protected function applyStyle(\PHPExcel_Worksheet $sheet, $maxColumn)
    {
        $style = [
            'font' => [
                'size' => 18,
                'bold' => true,
                'color' => [
                    'rgb' => 'ffffff'
                ]
            ],
            'fill' => [
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => [
                    'rgb' => 'ff0000'
                ]
            ]
        ];
        $sheet->getStyle('C1:C6')->applyFromArray($style);
        $sheet->getStyle(sprintf('%s%s:%s%s', 'A', 7, $maxColumn, 7))->applyFromArray($style);

        for ($i = 1; $i < 7; $i++) {
            $sheet->mergeCells(sprintf('%s%s:%s%s', 'C', $i, 'D', $i));
            $sheet->getRowDimension($i)->setRowHeight(30);
        }

        $startColumn = 'A';
        do {
            $sheet->getColumnDimension($startColumn)->setAutoSize(true);
            $startColumn = $this->incrementColumn($startColumn);
        } while ($startColumn !== $maxColumn);

        $sheet->getRowDimension(7)->setRowHeight(30);
    }
}
