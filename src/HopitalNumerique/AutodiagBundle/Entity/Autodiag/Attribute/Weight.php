<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;

/**
 * Weighted
 *
 * @ORM\Table(name="ad_autodiag_attribute_weight")
 * @ORM\Entity
 */
class Weight
{
    /**
     * @var Container
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container", inversedBy="attributesWeighted")
     * @ORM\JoinColumn(name="container_id", referencedColumnName="id")
     */
    private $container;

    /**
     * @var Attribute
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    private $attribute;

    /**
     * Attribute weight by container
     *
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $weight;

    public function __construct(Container $container, Attribute $attribute, $weight = null)
    {
        $this->container = $container;
        $this->attribute = $attribute;
        $this->weight = $weight;
    }

    /**
     * Set weight
     *
     * @param $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }
}

