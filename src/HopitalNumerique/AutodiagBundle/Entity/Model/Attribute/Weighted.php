<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Model\Attribute;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Model\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Model\Container;

/**
 * Weighted
 *
 * @ORM\Table(name="ad_model_attribute_weighted")
 * @ORM\Entity
 */
class Weighted
{
    /**
     * @var Container
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Model\Container")
     * @ORM\JoinColumn(name="container_id", referencedColumnName="id")
     */
    private $container;

    /**
     * @var Attribute
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Model\Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    private $attribute;

    /**
     * Attribute weight by container
     *
     * @var float
     *
     * @ORM\Column(type="decimal")
     */
    private $weight;

    public function __construct(Container $container, Attribute $attribute, $weight)
    {
        $this->container = $container;
        $this->attribute = $attribute;
        $this->weight = $weight;
    }
}

