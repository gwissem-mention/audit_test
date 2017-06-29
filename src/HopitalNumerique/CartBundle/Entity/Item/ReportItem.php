<?php

namespace HopitalNumerique\CartBundle\Entity\Item;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Table(name="hn_cart_report_item")
 * @ORM\Entity(repositoryClass="HopitalNumerique\CartBundle\Repository\ReportItemRepository")
 */
class ReportItem extends Item
{

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\CartBundle\Entity\Report", inversedBy="items")
     */
    protected $report;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $position = 0;

    /**
     * ReportItem constructor.
     *
     * @param $objectType
     * @param $objectId
     * @param Report $report
     */
    public function __construct($objectType, $objectId, Report $report = null)
    {
        $this->objectType = $objectType;
        $this->objectId = $objectId;
        $this->report = $report;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report|null $report
     *
     * @return ReportItem
     */
    public function setReport(Report $report = null)
    {
        $this->report = $report;
        if (!is_null($report)) {
            $report->addItem($this);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return ReportItem
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
