<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

/**
 * Container
 *
 * @ORM\Table(name="ad_autodiag_container")
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "category" = "\HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Category",
 *     "chapter" = "\HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter"
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
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container", cascade={"all"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @var Autodiag
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag")
     * @ORM\JoinColumn(name="autodiag_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $autodiag;

    public function setId($id)
    {
        $this->id = $id;
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
    public function setParent(Container $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get model
     *
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * Set Model
     *
     * @param Autodiag $autodiag
     * @return $this
     */
    public function setAutodiag(Autodiag $autodiag)
    {
        $this->autodiag = $autodiag;

        return $this;
    }
}
