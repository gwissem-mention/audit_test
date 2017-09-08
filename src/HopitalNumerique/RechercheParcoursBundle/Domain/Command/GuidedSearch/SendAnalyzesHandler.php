<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command\GuidedSearch;

use Nodevo\MailBundle\Manager\MailManager;
use HopitalNumerique\RechercheParcoursBundle\Service\Risk\Export\SynthesisCSVExport;
use HopitalNumerique\RechercheParcoursBundle\Service\Risk\Export\SynthesisExcelExport;

/**
 * Class SendAnalyzesHandler
 */
class SendAnalyzesHandler
{
    protected $mailManager;

    protected $excelExporter;

    protected $csvExporter;

    /**
     * SendAnalyzesHandler constructor.
     *
     * @param MailManager          $mailManager
     * @param SynthesisExcelExport $excelExporter
     * @param SynthesisCSVExport   $csvExporter
     */
    public function __construct(
        MailManager $mailManager,
        SynthesisExcelExport $excelExporter,
        SynthesisCSVExport $csvExporter
    ) {
        $this->mailManager = $mailManager;
        $this->excelExporter = $excelExporter;
        $this->csvExporter = $csvExporter;
    }


    /**
     * @param SendAnalyzesCommand $command
     */
    public function handle(SendAnalyzesCommand $command)
    {
        ob_start();
        $this->excelExporter->exportGuidedSearch($command->guidedSearch, $command->user);
        $excelFile = ob_get_contents();
        ob_end_clean();

        ob_start();
        $this->csvExporter->exportGuidedSearch($command->guidedSearch, $command->user);
        $csvFile = ob_get_contents();
        ob_end_clean();

        $this->mailManager->sendGuidedSearchAnalyzes(
            $command->sender,
            $command->recipient,
            $command->subject,
            $command->body,
            $excelFile,
            $csvFile
        );
    }
}
