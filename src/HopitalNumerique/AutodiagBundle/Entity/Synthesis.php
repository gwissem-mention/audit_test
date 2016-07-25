<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Synthesis
 *
 * @ORM\Table(name="ad_synthesis")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\SynthesisRepository")
 */
class Synthesis
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
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validatedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var Autodiag
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag")
     * @ORM\JoinColumn(name="autodiag_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $autodiag;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry",
     *     mappedBy="synthesis",
     *     cascade={"persist"}
     * )
     */
    private $entries;

    private function __construct(Autodiag $autodiag)
    {
        $this->entries = new ArrayCollection();
        $this->autodiag = $autodiag;

        $now = new \DateTime();
        $this->name = $now->format('d/m/Y H:i');
    }

    /**
     * Create new Synthesis
     *
     * @param Autodiag $autodiag
     * @return Synthesis
     */
    public static function create(Autodiag $autodiag)
    {
        $synthesis = new self($autodiag);

        return $synthesis;
    }

    /**
     * Create synthesis from other syntheses
     *
     * @param Autodiag $autodiag
     * @param Synthesis[] $syntheses
     * @return Synthesis
     */
    public static function createFromSynthesis(Autodiag $autodiag, $syntheses)
    {
        $synthesis = new self($autodiag);

        foreach ($syntheses as $synthesis) {
            foreach ($synthesis->getEntries() as $entry) {
                $synthesis->addEntry(
                    clone($entry)
                );
            }
        }

        return $synthesis;
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get validation datetime
     *
     * @return \DateTime
     */
    public function getValidatedAt()
    {
        return $this->validatedAt;
    }

    /**
     * Set validation datetime
     *
     * @param \DateTime $validatedAt
     *
     * @return $this
     */
    public function setValidatedAt($validatedAt)
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * Get Entires
     *
     * @return Collection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Add entry
     *
     * @param AutodiagEntry $entry
     * @return $this
     */
    public function addEntry(AutodiagEntry $entry)
    {
        $this->entries->add($entry);

        if (null === $this->getUpdatedAt() || $entry->getUpdatedAt() > $this->getUpdatedAt()) {
            $this->setUpdatedAt($entry->getUpdatedAt());
        }

        return $this;
    }

    /**
     * Remove entry
     *
     * @param AutodiagEntry $entry
     * @return $this
     */
    public function removeEntry(AutodiagEntry $entry)
    {
        $this->entries->removeElement($entry);

        return $this;
    }
}

