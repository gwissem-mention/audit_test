<?php

namespace HopitalNumerique\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatRecherche
 *
 * @ORM\Table(name="hn_statistiques_recherche")
 * @ORM\Entity(repositoryClass="HopitalNumerique\StatBundle\Repository\StatRechercheRepository")
 */
class StatRecherche
{
    /**
     * @var integer
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
     * @var integer
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
     * @var boolean
     *
     * @ORM\Column(name="stat_is_requete_saved", type="boolean", options = {"comment" = "Viens d une requete enregistrée en base ?"})
     */
    protected $isRequeteSaved;

    /**
     * @var integer
     * 
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_statistiques_recherche_requete",
     *      joinColumns={ @ORM\JoinColumn(name="stat_id", referencedColumnName="stat_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id")}
     * )
     */
    protected $references;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", nullable=true)
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->references = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set date
     *
     * @param \DateTime $date
     * @return StatRecherche
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Add references
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $references
     * @return StatRecherche
     */
    public function addReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $references)
    {
        $this->references[] = $references;

        return $this;
    }

    /**
     * Remove references
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $references
     */
    public function removeReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $references)
    {
        $this->references->removeElement($references);
    }

    /**
     * Get references
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Set references
     *
     * @param array(Reference) $references
     * @return StatRecherche
     */
    public function setReferences($references)
    {
        $this->references = $references;

        return $this;
    }

    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return StatRecherche
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \HopitalNumerique\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set nbResultats
     *
     * @param integer $nbResultats
     * @return StatRecherche
     */
    public function setNbResultats($nbResultats)
    {
        $this->nbResultats = $nbResultats;

        return $this;
    }

    /**
     * Get nbResultats
     *
     * @return integer 
     */
    public function getNbResultats()
    {
        return $this->nbResultats;
    }

    /**
     * Set requete
     *
     * @param string $requete
     * @return StatRecherche
     */
    public function setRequete($requete)
    {
        $this->requete = $requete;

        return $this;
    }

    /**
     * Get requete
     *
     * @return string 
     */
    public function getRequete()
    {
        return $this->requete;
    }

    /**
     * Set isRequeteSaved
     *
     * @param boolean $isRequeteSaved
     * @return StatRecherche
     */
    public function setIsRequeteSaved($isRequeteSaved)
    {
        $this->isRequeteSaved = $isRequeteSaved;

        return $this;
    }

    /**
     * Get isRequeteSaved
     *
     * @return boolean 
     */
    public function getIsRequeteSaved()
    {
        return $this->isRequeteSaved;
    }
}
