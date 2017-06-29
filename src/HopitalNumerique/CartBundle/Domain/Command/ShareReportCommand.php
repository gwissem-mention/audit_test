<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Entity\Report;

class ShareReportCommand
{
    const TYPE_SHARE = 'share';
    const TYPE_COPY = 'copy';

    /**
     * @var string $type
     */
    public $type;

    /**
     * @var Report $report
     */
    public $report;

    /**
     * @var string $targetEmail
     */
    public $targetEmail;

    /**
     * @var User $user
     */
    public $user;

    /**
     * ShareReportCommand constructor.
     *
     * @param string $type
     * @param Report $report
     * @param User $user
     */
    public function __construct($type, Report $report, User $user)
    {
        $this->type = $type;
        $this->report = $report;
        $this->user = $user;
    }
}
