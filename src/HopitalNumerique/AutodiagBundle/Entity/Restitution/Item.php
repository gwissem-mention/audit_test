<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Restitution;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Reference;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Item.
 *
 * @ORM\Table(name="ad_restitution_item")
 * @ORM\Entity
 */
class Item
{
    const RESPONSE_TYPE = 'reponses';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotNull
     * @Assert\Expression("this.getType() in ['jauge', 'processus', 'radar', 'tableau', 'plan_action', 'histogramme', 'score', 'meteo', 'reponses']")
     */
    private $type;

    /**
     * @var int
     * @ORM\Column(type="integer", name="x")
     * @Assert\NotNull
     * @Assert\GreaterThan(0)
     */
    private $row;

    /**
     * @var int
     * @ORM\Column(type="integer", name="y")
     * @Assert\NotNull
     * @Assert\GreaterThan(0)
     */
    private $column;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container")
     * @ORM\JoinTable(
     *     name="ad_restitution_item_container",
     *     joinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="container_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $containers;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotNull
     * @Assert\Expression("this.getPriority() in ['priorisé', 'questionnaire']")
     */
    private $priority;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Restitution\Category", inversedBy="items", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\Valid
     */
    private $category;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Reference")
     * @ORM\JoinTable(
     *     name="ad_restitution_item_reference",
     *     joinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="reference_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $references;

    public function __construct()
    {
        $this->containers = new ArrayCollection();
        $this->references = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Item
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @param int $row
     *
     * @return Item
     */
    public function setRow($row)
    {
        $this->row = $row;

        return $this;
    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param int $column
     *
     * @return Item
     */
    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @return array
     */
    public function getContainers()
    {
        $containers = (array) $this->containers->getValues();

        usort($containers, function (Container $a, Container $b) {
            return $a->getOrder() > $b->getOrder();
        });

        return $containers;
    }

    /**
     * @param Container $container
     *
     * @internal param Collection $containers
     *
     * @return $this
     */
    public function addContainer(Container $container)
    {
        $this->containers->add($container);

        return $this;
    }

    /**
     * @param Container $container
     *
     * @return $this
     */
    public function removeContainer(Container $container)
    {
        $this->containers->remove($container);

        return $this;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     *
     * @return $this
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @param Reference $reference
     *
     * @return $this
     */
    public function addReference(Reference $reference)
    {
        $this->references->add($reference);

        return $this;
    }

    /**
     * @param Reference $reference
     *
     * @return $this
     */
    public function removeReference(Reference $reference)
    {
        $this->references->removeElement($reference);

        return $this;
    }
}
