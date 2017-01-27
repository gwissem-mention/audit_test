<?php

namespace HopitalNumerique\StatBundle\Service;

use HopitalNumerique\StatBundle\Entity\ErrorUrl;
use HopitalNumerique\StatBundle\Repository\ErrorUrlRepository;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ErrorUrlExporter
{
    /** @var ErrorUrlRepository $errorUrlRepository */
    private $errorUrlRepository;

    protected $row = 1;

    public function __construct(ErrorUrlRepository $errorUrlRepository)
    {
        $this->errorUrlRepository = $errorUrlRepository;
    }

    /**
     * @return BinaryFileResponse
     */
    public function export()
    {
        $errorUrls = $this->errorUrlRepository->findBy([], ['domain' => 'ASC']);

        $excel = new \PHPExcel();
        $sheet = $this->addSheet($excel, 'erreur_url');
        $this->writeErrorUrlHeaders($sheet);

        $excel->removeSheetByIndex(0);

        foreach ($errorUrls as $errorUrl) {
            $this->writeErrorUrlRow($sheet, $errorUrl);
        }

        return $this->getFileResponse($excel, 'export-erreurs-url');
    }

    /**
     * @param \PHPExcel $excel
     * @param           $title
     *
     * @return \PHPExcel_Worksheet
     */
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

    /**
     * @param \PHPExcel_Worksheet $sheet
     * @param                     $row
     */
    protected function addRow(\PHPExcel_Worksheet $sheet, $row)
    {
        $col = 'A';
        foreach ($row as $cell) {
            $sheet->setCellValue($col . $this->row, $cell);
            $col++;
        }
        $this->row++;
    }

    /**
     * @param \PHPExcel $excel
     *
     * @return \PHPExcel_Writer_Excel2007
     */
    protected function getWriter($excel)
    {
        return new \PHPExcel_Writer_Excel2007($excel);
    }

    /**
     * @param \PHPExcel $excel
     * @param           $name
     * @param string    $prefix
     *
     * @return BinaryFileResponse
     */
    protected function getFileResponse(\PHPExcel $excel, $name, $prefix = '')
    {
        $file = stream_get_meta_data(tmpfile())['uri'];
        $this->getWriter($excel)->save($file);

        $title = new Chaine($name);

        $response = new BinaryFileResponse($file);

        $fullTitle = $prefix === '' ? $title->minifie() . '.xlsx' : $prefix . '_' . $title->minifie() . '.xlsx';

        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fullTitle);

        return $response;
    }

    /**
     * @param \PHPExcel_Worksheet $sheet
     */
    private function writeErrorUrlHeaders(\PHPExcel_Worksheet $sheet)
    {
        $columns = [
            'Domaine',
            'Id de l\'objet',
            'Titre de l\'objet',
            'Id du contenu',
            'Titre du contenu',
            'URL de l\'objet ou du contenu',
            'URL testée',
            'Statut',
            'Code',
        ];

        $this->addRow($sheet, $columns);
    }

    /**
     * @param \PHPExcel_Worksheet $sheet
     * @param ErrorUrl            $errorUrl
     */
    private function writeErrorUrlRow(\PHPExcel_Worksheet $sheet, ErrorUrl $errorUrl)
    {
        $errorUrlData = [];

        $errorUrlData[] = $errorUrl->getDomain()->getNom();
        $errorUrlData[] = $errorUrl->getObject()->getId();
        $errorUrlData[] = $errorUrl->getObject()->getTitre();
        if (!is_null($errorUrl->getContent())) {
            $errorUrlData[] = $errorUrl->getContent()->getId();
            $errorUrlData[] = $errorUrl->getContent()->getTitre();
            $errorUrlData[] = $errorUrl->getDomain()->getUrl().'/publication/'.$errorUrl->getObject()->getId()
                              .'/'
                              .$errorUrl->getContent()->getId();
        } else {
            $errorUrlData[] = null;
            $errorUrlData[] = null;
            $errorUrlData[] = $errorUrl->getDomain()->getUrl() . '/publication/' . $errorUrl->getObject()->getId();
        }
        $errorUrlData[] = $errorUrl->getCheckedUrl();
        $errorUrlData[] = $errorUrl->getState() ? 'Valide' : 'Non valide';
        $errorUrlData[] = $errorUrl->getCode();

        $this->addRow($sheet, $errorUrlData);
    }
}
