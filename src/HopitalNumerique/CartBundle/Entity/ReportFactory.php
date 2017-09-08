<?php

namespace HopitalNumerique\CartBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\CartBundle\Entity\Item\ReportFactoryItem;
use HopitalNumerique\CartBundle\Entity\Item\ReportItem;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Table(name="hn_cart_report_factory")
 * @ORM\Entity(repositoryClass="HopitalNumerique\CartBundle\Repository\ReportFactoryRepository")
 */
class ReportFactory implements \JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $owner;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\CartBundle\Entity\Report")
     */
    protected $report;

    /**
     * @var ReportFactoryItem[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="HopitalNumerique\CartBundle\Entity\Item\ReportFactoryItem", mappedBy="reportFactory", orphanRemoval=true, cascade={"all"})
     */
    protected $factoryItems;

    /**
     * Report constructor.
     *
     * @param User $owner
     * @param Report|null $report
     */
    public function __construct(User $owner, Report $report = null)
    {
        $this->owner = $owner;
        $this->report = $report;
        $this->factoryItems = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     *
     * @return ReportFactory
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     *
     * @return ReportFactory
     */
    public function setReport(Report $report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return ReportFactoryItem[]|ArrayCollection
     */
    public function getFactoryItems()
    {
        return $this->factoryItems;
    }

    /**
     * @param ReportFactoryItem $item
     *
     * @return ReportFactory
     */
    public function addFactoryItem(ReportFactoryItem $item)
    {
        if (!$this->factoryItems->contains($item)) {
            $this->factoryItems->add($item);
        }

        return $this;
    }

    /**
     * @return ReportItem[]
     */
    public function getReportItems()
    {
        $items = [];
        foreach ($this->getFactoryItems() as $factoryItem) {
            $items[] = $factoryItem->getItem();
        }

        return $items;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'report' => $this->getReport(),
            'factoryItems' => [],
        ];
    }
}
