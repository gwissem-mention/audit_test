<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Entity\ReportFactory;

class AddCartItemsToReportFactoryCommand
{
    /**
     * @var ReportFactory $reportFactory
     */
    public $reportFactory;

    /**
     * @var array $items
     */
    public $items = [];

    /**
     * @var User $user
     */
    public $user;

    /**
     * AddCartItemsToReportFactory constructor.
     *
     * @param ReportFactory $reportFactory
     * @param array $items
     * @param User $user
     */
    public function __construct(ReportFactory $reportFactory = null, array $items = [], User $user)
    {
        $this->reportFactory = $reportFactory;
        $this->items = $items;
        $this->user = $user;
    }
}
