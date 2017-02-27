<?php

namespace HopitalNumerique\AutodiagBundle\Service\Export;

use Doctrine\Common\Persistence\ObjectManager;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

abstract class AbstractExport
{
    protected $row = 1;

    protected $manager;

    /**
     * SurveyExport constructor.
     *
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    protected function addSheet(\PHPExcel $excel, $title)
    {
        foreach ($excel->getAllSheets() as $sheet) {
            if ($sheet->getTitle() == $title) {
                return $sheet;
            }
        }

        $sheet = $excel->createSheet();
        $sheet->setTitle($title);
        $sheet->setCodeName($title);

        return $sheet;
    }

    protected function addRow(\PHPExcel_Worksheet $sheet, $row)
    {
        $col = 'A';
        foreach ($row as $cell) {
            $sheet->setCellValue($col . $this->row, $cell);
            ++$col;
        }
        ++$this->row;
    }

    /**
     * Get Excel writer.
     *
     * @param \PHPExcel $excel
     *
     * @return \PHPExcel_Writer_Excel2007
     */
    protected function getWriter($excel)
    {
        return new \PHPExcel_Writer_Excel2007($excel);
    }

    protected function getFileResponse(\PHPExcel $excel, $name, $prefix = '')
    {
        $file = stream_get_meta_data(tmpfile())['uri'];
        $this->getWriter($excel)->save($file);

        $title = new Chaine($name);

        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $prefix . '_' . $title->minifie() . '.xlsx'
        );

        return $response;
    }
}
