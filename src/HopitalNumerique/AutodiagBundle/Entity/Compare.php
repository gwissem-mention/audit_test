<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Restitution.
 *
 * @ORM\Table(name="ad_compare")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\CompareRepository")
 */
class Compare
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
     * @var Synthesis
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Synthesis")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $synthesis;

    /**
     * @var Synthesis
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Synthesis")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $reference;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Compare constructor.
     *
     * @param Synthesis $synthesis
     * @param Synthesis $reference
     */
    public function __construct(Synthesis $synthesis, Synthesis $reference)
    {
        $this->synthesis = $synthesis;
        $this->reference = $reference;
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
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

    /**
     * @return Synthesis
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
