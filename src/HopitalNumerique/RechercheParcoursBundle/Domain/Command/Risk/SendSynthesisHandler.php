<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command\Risk;

use HopitalNumerique\RechercheParcoursBundle\Service\Risk\RiskSynthesisPDFExport;
use Nodevo\MailBundle\Manager\MailManager;

class SendSynthesisHandler
{
    /**
     * @var RiskSynthesisPDFExport $exportGenerator
     */
    protected $exportGenerator;

    /**
     * @var MailManager $mailManager
     */
    protected $mailManager;

    /**
     * @var \Swift_Mailer $mailer
     */
    protected $mailer;

    /**
     * SendSynthesisHandler constructor.
     *
     * @param RiskSynthesisPDFExport $exportGenerator
     * @param MailManager $mailManager
     * @param \Swift_Mailer $mailer
     */
    public function __construct(RiskSynthesisPDFExport $exportGenerator, MailManager $mailManager, \Swift_Mailer $mailer)
    {
        $this->exportGenerator = $exportGenerator;
        $this->mailManager = $mailManager;
        $this->mailer = $mailer;
    }

    /**
     * @param SendSynthesisCommand $sendSynthesisCommand
     */
    public function handle(SendSynthesisCommand $sendSynthesisCommand)
    {
        $filepath = $this->exportGenerator->generatePDFFile($sendSynthesisCommand->guidedSearch, $sendSynthesisCommand->user);

        $mail = $this->mailManager->sendGuidedSearchSynthesis($sendSynthesisCommand->subject, $sendSynthesisCommand->sender, $sendSynthesisCommand->recipient, $sendSynthesisCommand->content, $filepath);
        $this->mailer->send($mail);
    }
}
