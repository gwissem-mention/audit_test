<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Entity\ReportFactory;

class ReorderReportFactoryItemsCommand
{
    /**
     * @var ReportFactory $reportFactory
     */
    public $reportFactory;

    /**
     * @var User $owner
     */
    public $owner;

    /**
     * @var array $itemsOrder
     */
    public $itemsOrder;

    /**
     * ReorderReportFactoryItemsCommand constructor.
     *
     * @param User $owner
     * @param ReportFactory $reportFactory
     * @param array $itemsOrder
     */
    public function __construct(User $owner, ReportFactory $reportFactory, $itemsOrder)
    {
        $this->owner = $owner;
        $this->reportFactory = $reportFactory;
        $this->itemsOrder = $itemsOrder;
    }
}
