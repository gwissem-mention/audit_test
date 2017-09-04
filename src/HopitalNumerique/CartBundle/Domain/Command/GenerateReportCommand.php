<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Entity\ReportFactory;
use HopitalNumerique\CartBundle\Enum\ReportColumnsEnum;

class GenerateReportCommand
{
    /**
     * @var ReportFactory $reportFactory
     */
    public $reportFactory;

    /**
     * @var string $name
     */
    public $name;

    /**
     * @var User $owner
     */
    public $owner;

    /**
     * @var array
     */
    public $columns = [ReportColumnsEnum::RESUME_COLUMN];

    /**
     * EditReportFactoryCommand constructor.
     *
     * @param User $owner
     * @param ReportFactory $reportFactory
     */
    public function __construct(User $owner, ReportFactory $reportFactory = null)
    {
        $this->owner = $owner;
        $this->reportFactory = $reportFactory;
    }
}
