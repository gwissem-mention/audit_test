<?php

namespace HopitalNumerique\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Allows to store statistics about guided search
 *
 * @ORM\Table(name="hn_statistics_guided_search")
 * @ORM\Entity(repositoryClass="HopitalNumerique\StatBundle\Repository\GuidedSearchStatRepository")
 */
class GuidedSearchStat
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="usr_id")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="session_identifier", type="text", nullable=true)
     */
    protected $sessionIdentifier;

    /**
     * @var Domaine
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="dom_id")
     */
    protected $domain;

    /**
     * @var RechercheParcoursGestion
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion", cascade={"persist"})
     * @ORM\JoinColumn(name="recherche_parcours_gestion_id", referencedColumnName="rrpg_id")
     */
    protected $entry;

    /**
     * @var RechercheParcours
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours", cascade={"persist"})
     * @ORM\JoinColumn(name="recherche_parcours_id", referencedColumnName="rrp_id")
     */
    protected $path;

    /**
     * @var RechercheParcoursDetails
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails", cascade={"persist"})
     * @ORM\JoinColumn(name="recherche_parcours_details_id", referencedColumnName="rrpd_id")
     */
    protected $pathStep;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="reference_id", referencedColumnName="ref_id")
     */
    protected $pathSubStep;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getSessionIdentifier()
    {
        return $this->sessionIdentifier;
    }

    /**
     * @param string $sessionIdentifier
     */
    public function setSessionIdentifier($sessionIdentifier)
    {
        $this->sessionIdentifier = $sessionIdentifier;
    }

    /**
     * @return Domaine
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param Domaine $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return RechercheParcoursGestion
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param RechercheParcoursGestion $entry
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
    }

    /**
     * @return RechercheParcours
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param RechercheParcours $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return RechercheParcoursDetails
     */
    public function getPathStep()
    {
        return $this->pathStep;
    }

    /**
     * @param RechercheParcoursDetails $pathStep
     */
    public function setPathStep($pathStep)
    {
        $this->pathStep = $pathStep;
    }

    /**
     * @return Reference
     */
    public function getPathSubStep()
    {
        return $this->pathSubStep;
    }

    /**
     * @param Reference $pathSubStep
     */
    public function setPathSubStep($pathSubStep)
    {
        $this->pathSubStep = $pathSubStep;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }
}
