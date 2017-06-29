<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use HopitalNumerique\CartBundle\Service\ReportGenerator\ReportGenerator;
use Nodevo\MailBundle\Manager\MailManager;

class SendReportCommandHandler
{
    /**
     * @var \Swift_Mailer $mailer
     */
    protected $mailer;

    /**
     * @var MailManager $mailManager
     */
    protected $mailManager;

    /**
     * @var ReportGenerator $reportGenerator
     */
    protected $reportGenerator;

    /**
     * SendReportCommandHandler constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param MailManager $mailManager
     * @param ReportGenerator $reportGenerator
     */
    public function __construct(\Swift_Mailer $mailer, MailManager $mailManager, ReportGenerator $reportGenerator)
    {
        $this->mailer = $mailer;
        $this->mailManager = $mailManager;
        $this->reportGenerator = $reportGenerator;
    }

    /**
     * @param SendReportCommand $command
     */
    public function handle(SendReportCommand $command)
    {
        $mail = $this->mailManager->sendCartReport(
            $command->subject,
            $command->sender,
            $command->recipient,
            $command->content,
            $this->reportGenerator->getReportFile($command->report)
        );
        $this->mailer->send($mail);
    }
}
