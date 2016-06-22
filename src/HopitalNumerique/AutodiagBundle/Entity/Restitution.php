<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Category;

/**
 * Restitution
 *
 * @ORM\Table(name="ad_restitution")
 * @ORM\Entity
 */
class Restitution
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
     * @var Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Restitution\Category",
     *     mappedBy="restitution",
     *     cascade={"persist"}
     * )
     */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

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
     * @return Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function addCategory(Category $category)
    {
        $this->categories->add($category);
        $category->setRestitution($this);

        return $this;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category);

        return $this;
    }
}

