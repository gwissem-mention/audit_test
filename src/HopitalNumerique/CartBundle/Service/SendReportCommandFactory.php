<?php

namespace HopitalNumerique\CartBundle\Service;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Domain\Command\SendReportCommand;
use Nodevo\MailBundle\Entity\Mail;
use Nodevo\MailBundle\Manager\MailManager;

class SendReportCommandFactory
{
    /**
     * @var MailManager $mailManager
     */
    protected $mailManager;

    /**
     * SendReportCommandFactory constructor.
     *
     * @param MailManager $mailManager
     */
    public function __construct(MailManager $mailManager)
    {
        $this->mailManager = $mailManager;
    }

    /**
     * @param User $user
     * @param Report $report
     *
     * @return SendReportCommand
     */
    public function build(User $user, Report $report = null)
    {
        $command = new SendReportCommand($report);
        $command->sender = $user->getEmail();
        $command->report = $report;

        /** @var Mail $mail */
        if (!is_null($mail = $this->mailManager->getCartReportMail())) {
            $command->subject = $mail->getObjet();
            $command->content = $mail->getBody();
        }

        return $command;
    }
}
