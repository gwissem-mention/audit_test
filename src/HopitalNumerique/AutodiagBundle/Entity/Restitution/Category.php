<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Restitution;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Restitution;

/**
 * Category
 *
 * @ORM\Table(name="ad_restitution_category")
 * @ORM\Entity
 */
class Category
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var Restitution
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Restitution", inversedBy="categories")
     * @ORM\JoinColumn(name="restitution_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $restitution;

    /**
     * @var Collection
     * @ORM\OneToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Restitution\Item",
     *     mappedBy="category",
     *     cascade={"persist"},
     *     fetch="EAGER"
     * )
     * @ORM\OrderBy({"row" = "ASC", "column" = "ASC"})
     */
    private $items;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return Restitution
     */
    public function getRestitution()
    {
        return $this->restitution;
    }

    /**
     * @param Restitution $restitution
     * @return $this
     */
    public function setRestitution(Restitution $restitution)
    {
        $this->restitution = $restitution;

        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }
}

