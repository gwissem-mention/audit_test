<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Resultat
 *
 * @ORM\Table("hn_outil_resultat")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\ResultatRepository")
 */
class Resultat
{
    /**
     * @var integer
     *
     * @ORM\Column(name="res_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="res_date_last_save", type="datetime")
     */
    private $dateLastSave;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="res_date_validation", type="datetime", nullable=true)
     */
    private $dateValidation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="res_date_creation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var float
     *
     * @ORM\Column(name="res_taux_remplissage", type="float")
     */
    private $tauxRemplissage;

    /**
     * @var string
     *
     * @ORM\Column(name="res_pdf", type="string", length=255, nullable=true)
     */
    private $pdf;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut", referencedColumnName="ref_id")
     */
    protected $statut;

    /**
     * @ORM\ManyToOne(targetEntity="Outil", cascade={"persist"}, inversedBy="resultats")
     * @ORM\JoinColumn(name="out_id", referencedColumnName="out_id", onDelete="CASCADE")
     */
    protected $outil;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE", nullable=true)
     */
    protected $user;
    
    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\AutodiagBundle\Entity\Reponse", mappedBy="resultat", cascade={"persist"})
     */
    protected $reponses;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->tauxRemplissage = 0;
        $this->dateCreation    = new \DateTime;
        $this->reponses        = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pdf             = null;
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
     * Set dateLastSave
     *
     * @param \DateTime $dateLastSave
     * @return Resultat
     */
    public function setDateLastSave($dateLastSave)
    {
        $this->dateLastSave = $dateLastSave;

        return $this;
    }

    /**
     * Get dateLastSave
     *
     * @return \DateTime 
     */
    public function getDateLastSave()
    {
        return $this->dateLastSave;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime $dateCreation
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }
    
    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    /**
     * Set dateValidation
     *
     * @param \DateTime $dateValidation
     * @return Resultat
     */
    public function setDateValidation($dateValidation)
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }

    /**
     * Get dateValidation
     *
     * @return \DateTime 
     */
    public function getDateValidation()
    {
        return $this->dateValidation;
    }

    /**
     * Set tauxRemplissage
     *
     * @param float $tauxRemplissage
     * @return Resultat
     */
    public function setTauxRemplissage($tauxRemplissage)
    {
        $this->tauxRemplissage = $tauxRemplissage;

        return $this;
    }

    /**
     * Get tauxRemplissage
     *
     * @return float 
     */
    public function getTauxRemplissage()
    {
        return $this->tauxRemplissage;
    }

    /**
     * Get pdf
     *
     * @return string $pdf
     */
    public function getPdf()
    {
        return $this->pdf;
    }
    
    /**
     * Set pdf
     *
     * @param string $pdf
     */
    public function setPdf($pdf)
    {
        $this->pdf = $pdf;
        return $this;
    }

    /**
     * Get statut
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $statut
     */
    public function getStatut()
    {
        return $this->statut;
    }
    
    /**
     * Set statut
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $statut
     */
    public function setStatut(\HopitalNumerique\ReferenceBundle\Entity\Reference $statut)
    {
        $this->statut = $statut;
        return $this;
    }

    /**
     * Get outil
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Outil $outil
     */
    public function getOutil()
    {
        return $this->outil;
    }
    
    /**
     * Set outil
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outil
     */
    public function setOutil(\HopitalNumerique\AutodiagBundle\Entity\Outil $outil)
    {
        $this->outil = $outil;
        return $this;
    }

    /**
     * Get user
     *
     * @return \HopitalNumerique\UserBundle\Entity\User $user
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user)
    {
        $this->user = $user;
        return $this;
    }
    
    /**
     * Get reponses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReponses()
    {
        return $this->reponses;
    }
}