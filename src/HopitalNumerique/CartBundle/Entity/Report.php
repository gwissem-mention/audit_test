<?php

namespace HopitalNumerique\CartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use HopitalNumerique\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\CartBundle\Entity\Item\ReportItem;

/**
 * @ORM\Table(name="hn_cart_report")
 * @ORM\Entity(repositoryClass="HopitalNumerique\CartBundle\Repository\ReportRepository")
 */
class Report implements \JsonSerializable
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
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Gedmo\Slug(fields={"name"}, unique=false)
     */
    protected $slug;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $owner;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @var ReportItem[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="HopitalNumerique\CartBundle\Entity\Item\ReportItem", mappedBy="report", cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $items;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $columns = [];

    /**
     * @var ReportSharing[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="HopitalNumerique\CartBundle\Entity\ReportSharing", mappedBy="report", cascade={"remove"})
     */
    protected $shares;

    /**
     * @var ReportSharing
     *
     * @ORM\OneToOne(targetEntity="HopitalNumerique\CartBundle\Entity\ReportSharing", cascade={"remove"}, inversedBy="copiedReport")
     */
    protected $sharedBy;

    /**
     * Report constructor.
     *
     * @param User $owner
     */
    public function __construct(User $owner)
    {
        $this->owner = $owner;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->items = new ArrayCollection();
        $this->shares = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Report
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
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
     * @return Report
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return Report
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return Report
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return ReportItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ReportItem $item
     *
     * @return Report
     */
    public function addItem(ReportItem $item)
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     *
     * @return Report
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return ArrayCollection|ReportSharing[]
     */
    public function getShares()
    {
        return $this->shares;
    }

    /**
     * @return ReportSharing
     */
    public function getSharedBy()
    {
        return $this->sharedBy;
    }

    /**
     * @param ReportSharing|null $sharedBy
     *
     * @return Report
     */
    public function setSharedBy(ReportSharing $sharedBy = null)
    {
        $this->sharedBy = $sharedBy;

        return $this;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
        ];
    }

    public function __clone()
    {
        $this->id = null;
        $this->sharedBy = null;
        $this->shares = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        $items = $this->getItems();
        $this->items = new ArrayCollection();

        foreach ($items as $item) {
            $clonedItem = clone $item;
            $clonedItem->setReport($this);
        }
    }
}
