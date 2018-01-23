<?php

namespace HopitalNumerique\StatBundle\Service;

use HopitalNumerique\StatBundle\Entity\ErrorUrl;
use HopitalNumerique\StatBundle\Repository\ErrorUrlRepository;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Translation\TranslatorInterface;

class ErrorUrlExporter
{
    /** @var ErrorUrlRepository $errorUrlRepository */
    private $errorUrlRepository;

    /** @var TranslatorInterface $translator */
    private $translator;

    protected $row = 1;

    public function __construct(ErrorUrlRepository $errorUrlRepository, TranslatorInterface $translator)
    {
        $this->errorUrlRepository = $errorUrlRepository;
        $this->translator = $translator;
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
            ++$col;
        }
        ++$this->row;
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
            $this->translator->trans('errors_url.export.headers.domain'),
            $this->translator->trans('errors_url.export.headers.object_id'),
            $this->translator->trans('errors_url.export.headers.object_title'),
            $this->translator->trans('errors_url.export.headers.content_id'),
            $this->translator->trans('errors_url.export.headers.content_title'),
            $this->translator->trans('errors_url.export.headers.url_object_content'),
            $this->translator->trans('errors_url.export.headers.tested_url'),
            $this->translator->trans('errors_url.export.headers.state'),
            $this->translator->trans('errors_url.export.headers.code'),
            $this->translator->trans('errors_url.export.headers.last_checked_date'),
        ];

        $this->addRow($sheet, $columns);
    }

    /**
     * @param \PHPExcel_Worksheet $sheet
     * @param ErrorUrl            $errorUrl
     */
    private function writeErrorUrlRow(\PHPExcel_Worksheet $sheet, ErrorUrl $errorUrl)
    {
        $http_codes = [
            0 => 'Bad Request',
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended',
        ];

        $errorUrlData = [];

        $errorUrlData[] = $errorUrl->getDomain()->getNom();
        $errorUrlData[] = $errorUrl->getObject()->getId();
        $errorUrlData[] = $errorUrl->getObject()->getTitre();
        if (!is_null($errorUrl->getContent())) {
            $errorUrlData[] = $errorUrl->getContent()->getId();
            $errorUrlData[] = $errorUrl->getContent()->getTitre();
            $errorUrlData[] = $errorUrl->getDomain()->getUrl() . '/publication/' . $errorUrl->getObject()->getId()
                              . '/'
                              . $errorUrl->getContent()->getId();
        } else {
            $errorUrlData[] = null;
            $errorUrlData[] = null;
            $errorUrlData[] = $errorUrl->getDomain()->getUrl() . '/publication/' . $errorUrl->getObject()->getId();
        }
        $errorUrlData[] = $errorUrl->getCheckedUrl();
        $errorUrlData[] = $errorUrl->getState() ? 'Valide' : 'Non valide';
        $errorUrlData[] = $http_codes[$errorUrl->getCode()];
        $errorUrlData[] = $errorUrl->getLastCheckDate()->format('d-m-Y H:i:s');

        $this->addRow($sheet, $errorUrlData);
    }
}
