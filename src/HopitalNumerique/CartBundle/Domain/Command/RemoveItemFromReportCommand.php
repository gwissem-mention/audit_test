<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use HopitalNumerique\CartBundle\Entity\Item\ReportItem;
use HopitalNumerique\UserBundle\Entity\User;

class RemoveItemFromReportCommand
{
    /**
     * @var ReportItem $reportItem
     */
    public $reportItem;

    /**
     * @var User $owner
     */
    public $owner;

    /**
     * RemoveItemFromReport constructor.
     *
     * @param ReportItem $reportItem
     * @param User $owner
     */
    public function __construct(ReportItem $reportItem, User $owner)
    {
        $this->reportItem = $reportItem;
        $this->owner = $owner;
    }
}
