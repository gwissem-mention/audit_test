<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use HopitalNumerique\CartBundle\Entity\Report;
use Symfony\Component\Validator\Constraints as Assert;


class SendReportCommand
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $recipient;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $sender;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $subject;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $content;

    /**
     * @var Report
     */
    public $report;

    /**
     * SendReportCommand constructor.
     *
     * @param Report|null $report
     */
    public function __construct(Report $report = null)
    {
        $this->report = $report;
    }
}
