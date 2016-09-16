<?php

namespace HopitalNumerique\AutodiagBundle\Service\Export;

use Doctrine\Common\Persistence\ObjectManager;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item;
use \HopitalNumerique\AutodiagBundle\Model\Result\Item as ResultItem;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Service\RestitutionCalculator;
use HopitalNumerique\UserBundle\Entity\User;

class RestitutionItemExport extends AbstractExport
{
    protected $calculator;
    protected $gabaritPath;

    public function __construct(ObjectManager $manager, RestitutionCalculator $calculator, $gabaritPath)
    {
        parent::__construct($manager);

        $this->calculator = $calculator;
        $this->gabaritPath = $gabaritPath;
    }

    public function export(Synthesis $synthesis, Item $item, User $user)
    {
        $result = $this->calculator->computeItem($item, $synthesis);

        $XLSXDocument = new \PHPExcel_Reader_Excel2007();
        $excel = $XLSXDocument->load($this->gabaritPath);
        $excel->setActiveSheetIndex(0);

        $sheet = $excel->getActiveSheet();

        $this->writeHeader($sheet, $synthesis, $user);

        foreach ($result['items'] as $resultItem) {
            $this->writeResultItem($sheet, $resultItem);
        }

        return $this->getFileResponse($excel, $synthesis->getName(), 'plan_action');
    }



    protected function writeHeader(\PHPExcel_Worksheet $sheet, Synthesis $synthesis, User $user)
    {
        $this->addRow($sheet, [
            sprintf(
                'Autodiagnostic "%s" - "%s"',
                $synthesis->getAutodiag()->getTitle(),
                $synthesis->getName()
            )
        ]);

        $now = new \DateTime();
        $this->addRow($sheet, [
            sprintf(
                'Plan d\'action exporté le %s à %s par %s',
                $now->format('d/m/Y'),
                $now->format('H:i'),
                $user->getPrenom() . ' ' . $user->getNom()
            )
        ]);

        $this->row++;
    }

    protected function writeResultItem(\PHPExcel_Worksheet $sheet, ResultItem $item, ResultItem $parent = null)
    {
        $visible = false;

        if (null !== $item->getActionPlan()) {
            $visible = true;
        }

        foreach ($item->getAttributes() as $attribute) {
            if (null !== $attribute->getActionPlan()) {
                $visible = true;
                break;
            }
        }

        foreach ($item->getChildrens() as $children) {
            if (null !== $children->getActionPlan()) {
                $visible = true;
                break;
            }

            foreach ($children->getAttributes() as $attribute) {
                if (null !== $attribute->getActionPlan()) {
                    $visible = true;
                    break;
                }
            }
        }

        if (false === $visible) {
            return null;
        }

        $this->addRow($sheet, [
            $parent ? $parent->getLabel() : $item->getLabel(),
            $parent ? $item->getLabel() : '',
            '',
            '',
            $item->getActionPlan() ? $item->getActionPlan()->getDescription() : ''
        ]);

        foreach ($item->getAttributes() as $attribute) {
            if (null !== $attribute->getActionPlan()) {
                $this->addRow(
                    $sheet,
                    [
                        $parent ? $parent->getLabel() : $item->getLabel(),
                        $parent ? $item->getLabel() : '',
                        $attribute->label,
                        $attribute->responseText,
                        $attribute->getActionPlan() ? $attribute->getActionPlan()->getDescription() : ''
                    ]
                );
            }
        }

        foreach ($item->getChildrens() as $children) {
            $this->writeResultItem($sheet, $children, $item);
        }
    }

    protected function applyStyle(\PHPExcel_Worksheet $sheet)
    {
        $sheet->getStyle('A1:Z1')->applyFromArray([
            'font' => [
                'size' => 18,
                'bold' => true,
            ]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getStyle('A2:Z2')->applyFromArray([
            'font' => [
                'italic' => true,
            ]
        ]);

        $style = [
            'font' => [
//                'size' => 18,
                'bold' => true,
            ],
            'fill' => [
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => [
                    'rgb' => 'ff8080'
                ]
            ]
        ];

        $sheet->getStyle('A3:I3')->applyFromArray($style);

        $sheet->mergeCells('A1:Z1');
        $sheet->mergeCells('A2:Z2');
//        $sheet->getRowDimension($i)->setRowHeight(30);
    }
}
