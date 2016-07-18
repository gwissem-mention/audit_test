<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Autodiag entry - a response to an autodiag
 *
 * @ORM\Table(name="ad_entry")
 * @ORM\Entity
 */
class AutodiagEntry
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
     * @var Synthesis
     *
     * @ORM\ManyToOne(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Synthesis",
     *     inversedBy="entries",
     *     fetch="EAGER"
     * )
     * @ORM\JoinColumn(name="synthesis_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $synthesis;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry\Value", mappedBy="entry")
     */
    private $values;

    public function __construct(Synthesis $synthesis)
    {
        $this->synthesis = $synthesis;
        $this->values = new ArrayCollection();
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
     * @return Synthesis
     */
    public function getSynthesis()
    {
        return $this->synthesis;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function addValue($value)
    {
        $this->values->add($value);
    }
}

