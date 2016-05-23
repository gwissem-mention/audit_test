<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Container
 *
 * @ORM\Table(name="ad_model_container")
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "category" = "\HopitalNumerique\AutodiagBundle\Entity\Model\Container\Category",
 *     "chapter" = "\HopitalNumerique\AutodiagBundle\Entity\Model\Container\Chapter"
 * })
 */
abstract class Container
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
     * Code
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * Label
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @var Container
     *
     * @ORM\OneToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Model\Container")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

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
     * Get code
     *
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param mixed $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get label
     *
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param mixed $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get parent
     * @return Container
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent
     *
     * @param Container $parent
     * @return $this
     */
    public function setParent(Container $parent)
    {
        $this->parent = $parent;

        return $this;
    }
}
