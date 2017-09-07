<?php

namespace HopitalNumerique\CartBundle\Entity\Item;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Entity\ReportFactory;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Table(name="hn_cart_report_factory_item")
 * @ORM\Entity(repositoryClass="HopitalNumerique\CartBundle\Repository\ReportFactoryItemRepository")
 */
class ReportFactoryItem
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
     * @var ReportFactory
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\CartBundle\Entity\ReportFactory", inversedBy="factoryItems")
     */
    protected $reportFactory;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $owner;

    /**
     * @var ReportItem
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\CartBundle\Entity\Item\ReportItem")
     */
    protected $item;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * ReportFactoryItem constructor.
     *
     * @param ReportFactory $reportFactory
     * @param User $owner
     * @param ReportItem $item
     * @param int $position
     */
    public function __construct(ReportFactory $reportFactory, User $owner, ReportItem $item, $position)
    {
        $this->reportFactory = $reportFactory;
        $this->owner = $owner;
        $this->item = $item;
        $this->position = $position;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ReportFactory
     */
    public function getReportFactory()
    {
        return $this->reportFactory;
    }

    /**
     * @param ReportFactory $reportFactory
     *
     * @return ReportFactoryItem
     */
    public function setReportFactory(ReportFactory $reportFactory)
    {
        $this->reportFactory = $reportFactory;

        return $this;
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
     * @return ReportFactoryItem
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return ReportItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param ReportItem $item
     *
     * @return ReportFactoryItem
     */
    public function setItem(ReportItem $item)
    {
        $this->item = $item;

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
     * @return ReportFactoryItem
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
