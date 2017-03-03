<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Category;

/**
 * Restitution.
 *
 * @ORM\Table(name="ad_restitution")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\RestitutionRepository")
 */
class Restitution
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Score label.
     *
     * @var string
     *
     * @ORM\Column(name="score_label", type="string", nullable=true)
     */
    private $scoreLabel;

    /**
     * Score color.
     *
     * @var int
     *
     * @ORM\Column(name="score_color", type="string", length=50, nullable=true)
     */
    private $scoreColor;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Restitution\Category",
     *     mappedBy="restitution",
     *     cascade={"persist"},
     *     fetch="EAGER"
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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
     * @return Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param Category $category
     *
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
     *
     * @return $this
     */
    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return string
     */
    public function getScoreLabel()
    {
        return $this->scoreLabel;
    }

    /**
     * @param string $scoreLabel
     *
     * @return Restitution
     */
    public function setScoreLabel($scoreLabel)
    {
        $this->scoreLabel = $scoreLabel;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getScoreColor()
    {
        return $this->scoreColor;
    }

    /**
     * @param mixed $scoreColor
     *
     * @return Restitution
     */
    public function setScoreColor($scoreColor)
    {
        $this->scoreColor = $scoreColor;

        return $this;
    }
}
