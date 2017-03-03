<?php

namespace HopitalNumerique\ReportBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Report.
 *
 * @ORM\Table(name="hn_report")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ReportBundle\Repository\ReportRepository")
 */
class Report
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="L'url ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @ORM\Column(name="url", type="string", length=255)
     */
    protected $url;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Il doit y avoir une observation.")
     * @Nodevo\Javascript(class="validate[required]")
     * @ORM\Column(name="observations", type="text")
     */
    protected $observations;

    /**
     * @var string
     *
     * @ORM\Column(name="userAgent", type="string", length=255)
     */
    protected $userAgent;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE", nullable=true)
     */
    protected $user;

    /**
     * @var bool
     *
     * @ORM\Column(name="archive", type="boolean", length=255)
     */
    protected $archive;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(name="hn_domaine_gestions_report",
     *      joinColumns={ @ORM\JoinColumn(name="rep_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domaines;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->archive = false;
        $this->domaines = new ArrayCollection();
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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Report
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Report
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set observations.
     *
     * @param string $observations
     *
     * @return Report
     */
    public function setObservations($observations)
    {
        $this->observations = $observations;

        return $this;
    }

    /**
     * Get observations.
     *
     * @return string
     */
    public function getObservations()
    {
        return $this->observations;
    }

    /**
     * Set userAgent.
     *
     * @param string $userAgent
     *
     * @return Report
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get userAgent.
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set archive.
     *
     * @param bool $archive
     *
     * @return Report
     */
    public function setArchive($archive)
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * Get archive.
     *
     * @return bool
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Set user.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     *
     * @return Report
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \HopitalNumerique\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set one domaine and delete other.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine
     *
     * @return Report
     */
    public function setDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        $this->domaines->clear();
        $this->domaines->add($domaine);

        return $this;
    }

    /**
     * Add domaines.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     *
     * @return Report
     */
    public function addDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines[] = $domaines;

        return $this;
    }

    /**
     * Remove domaines.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     */
    public function removeDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines->removeElement($domaines);
    }

    /**
     * Get domaines.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDomaines()
    {
        return $this->domaines;
    }
}
