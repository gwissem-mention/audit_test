<?php

namespace HopitalNumerique\StatBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * StatRecherche.
 *
 * @ORM\Table(name="hn_statistiques_recherche")
 * @ORM\Entity(repositoryClass="HopitalNumerique\StatBundle\Repository\StatRechercheRepository")
 */
class StatRecherche
{
    /**
     * @var int
     *
     * @ORM\Column(name="stat_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stat_date", type="datetime")
     */
    protected $date;

    /**
     * @var int
     *
     * @ORM\Column(name="stat_nombre_resultat", type="smallint", options = {"comment" = "Nombre de résultat"})
     */
    protected $nbResultats;

    /**
     * @var string
     *
     * @ORM\Column(name="stat_requete", type="text")
     */
    protected $requete;

    /**
     * @var bool
     *
     * @ORM\Column(name="stat_is_requete_saved", type="boolean", options = {"comment" = "Viens d une requete enregistrée en base ?"})
     */
    protected $isRequeteSaved;

    /**
     * @var int
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_statistiques_recherche_requete",
     *      joinColumns={ @ORM\JoinColumn(name="stat_id", referencedColumnName="stat_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")}
     * )
     */
    protected $references;

    /**
     * @var string
     *
     * @ORM\Column(name="stat_categ_point_dur", type="string", length=255)
     */
    private $categPointDur;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="stat_session_id", type="text", nullable=true)
     */
    protected $sessionId;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->references = new ArrayCollection();
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
     * @return StatRecherche
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
     * Add references.
     *
     * @param Reference $references
     *
     * @return StatRecherche
     */
    public function addReference(Reference $references)
    {
        $this->references[] = $references;

        return $this;
    }

    /**
     * Remove references.
     *
     * @param Reference $references
     */
    public function removeReference(Reference $references)
    {
        $this->references->removeElement($references);
    }

    /**
     * Get references.
     *
     * @return Collection
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Set references.
     *
     * @param Reference[] $references
     *
     * @return StatRecherche
     */
    public function setReferences($references)
    {
        $this->references = $references;

        return $this;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return StatRecherche
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set nbResultats.
     *
     * @param int $nbResultats
     *
     * @return StatRecherche
     */
    public function setNbResultats($nbResultats)
    {
        $this->nbResultats = $nbResultats;

        return $this;
    }

    /**
     * Get nbResultats.
     *
     * @return int
     */
    public function getNbResultats()
    {
        return $this->nbResultats;
    }

    /**
     * Set requete.
     *
     * @param string $requete
     *
     * @return StatRecherche
     */
    public function setRequete($requete)
    {
        $this->requete = $requete;

        return $this;
    }

    /**
     * Get requete.
     *
     * @return string
     */
    public function getRequete()
    {
        return $this->requete;
    }

    /**
     * Set isRequeteSaved.
     *
     * @param bool $isRequeteSaved
     *
     * @return StatRecherche
     */
    public function setIsRequeteSaved($isRequeteSaved)
    {
        $this->isRequeteSaved = $isRequeteSaved;

        return $this;
    }

    /**
     * Get isRequeteSaved.
     *
     * @return bool
     */
    public function getIsRequeteSaved()
    {
        return $this->isRequeteSaved;
    }

    /**
     * Set categPointDur.
     *
     * @param string $categPointDur
     *
     * @return StatRecherche
     */
    public function setCategPointDur($categPointDur)
    {
        $this->categPointDur = $categPointDur;

        return $this;
    }

    /**
     * Get categPointDur.
     *
     * @return string
     */
    public function getCategPointDur()
    {
        return $this->categPointDur;
    }

    /**
     * Set sessionId.
     *
     * @param string $sessionId
     *
     * @return StatRecherche
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get sessionId.
     *
     * @return string
     */
    public function getsessionId()
    {
        return $this->sessionId;
    }
}
