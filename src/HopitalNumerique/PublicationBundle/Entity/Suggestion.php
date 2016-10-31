<?php

namespace HopitalNumerique\PublicationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Suggestion
 *
 * @ORM\Table(name="hn_suggestion")
 * @ORM\Entity(repositoryClass="HopitalNumerique\PublicationBundle\Repository\SuggestionRepository")
 */
class Suggestion
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
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 1,
     *     max = 255,
     * )
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $synthesis;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $referencing;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $link;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="state", referencedColumnName="ref_id")
     */
    private $state;

    /**
     * @var Domaine[]
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="hn_suggestion_domaine",
     *     joinColumns={@ORM\JoinColumn(name="sug_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    private $domains;

    public function __construct()
    {
        $this->creationDate = new \DateTime();
        $this->domains = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     *
     * @return $this
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     *
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return string
     */
    public function getSynthesis()
    {
        return $this->synthesis;
    }

    /**
     * @param string $synthesis
     * @return $this
     */
    public function setSynthesis($synthesis)
    {
        $this->synthesis = $synthesis;

        return $this;
    }

    /**
     * @return array
     */
    public function getReferencing()
    {
        return $this->referencing;
    }

    /**
     * @param array $referencing
     *
     * @return $this
     */
    public function setReferencing($referencing)
    {
        $this->referencing = $referencing;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return Reference
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param Reference $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return \HopitalNumerique\DomaineBundle\Entity\Domaine[]
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param Domaine $domaine
     *
     * @return $this
     */
    public function addDomain(Domaine $domaine)
    {
        $this->domains[] = $domaine;

        return $this;
    }

}
