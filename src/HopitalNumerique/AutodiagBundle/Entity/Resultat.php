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
     * @ORM\Column(name="res_id", type="integer", options = {"comment" = "ID du résultat"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="res_name", type="string", length=255, nullable=true, options = {"comment" = "Nom du résultat"})
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="res_date_last_save", type="datetime", options = {"comment" = "Date de dernière sauvegarde du résultat"})
     */
    private $dateLastSave;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="res_date_validation", type="datetime", nullable=true, options = {"comment" = "Date de validation du résultat"})
     */
    private $dateValidation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="res_date_creation", type="datetime", options = {"comment" = "Date de création du résultat"})
     */
    private $dateCreation;

    /**
     * @var float
     *
     * @ORM\Column(name="res_taux_remplissage", type="float", options = {"comment" = "Taux de remplissage du résultat"})
     */
    private $tauxRemplissage;

    /**
     * @var string
     *
     * @ORM\Column(name="res_pdf", type="string", length=255, nullable=true, options = {"comment" = "Lien pour le PDF du résultat"})
     */
    private $pdf;

    /**
     * @var string
     *
     * @ORM\Column(name="res_remarque", type="text", options = {"comment" = "Remarque sur le questionnaire"}, nullable=true)
     */
    private $remarque;

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
     * @var boolean
     *
     * @ORM\Column(name="res_synthese", type="boolean", options = {"comment" = "Est-ce une synthèse ?"})
     */
    protected $synthese;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\AutodiagBundle\Entity\Resultat")
     * @ORM\JoinTable(name="hn_outil_synthese",
     *      joinColumns={ @ORM\JoinColumn(name="syn_id", referencedColumnName="res_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="res_id", referencedColumnName="res_id", onDelete="CASCADE")}
     * )
     */
    protected $resultats;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->tauxRemplissage = 0;
        $this->dateCreation    = new \DateTime;
        $this->reponses        = new \Doctrine\Common\Collections\ArrayCollection();
        $this->resultats       = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pdf             = null;
        $this->remarque        = null;
        $this->synthese        = false;
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
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
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

    /**
     * Get synthese
     *
     * @return boolean $synthese
     */
    public function getSynthese()
    {
        return $this->synthese;
    }
    
    /**
     * Set synthese
     *
     * @param boolean $synthese
     */
    public function setSynthese($synthese)
    {
        $this->synthese = $synthese;
        return $this;
    }
    
    /**
     * Get remarque
     *
     * @return string $remarque
     */
    public function getRemarque()
    {
        return $this->remarque;
    }
    
    /**
     * Set remarque
     *
     * @param string $remarque
     */
    public function setRemarque($remarque)
    {
        $this->remarque = $remarque;
        return $this;
    }

    /**
     * Add resultat
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Resultat $resultat
     * @return Resultat
     */
    public function addResultat(\HopitalNumerique\AutodiagBundle\Entity\Resultat $resultat)
    {
        $this->resultats[] = $resultat;
    
        return $this;
    }

    /**
     * Remove resultat
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Resultat $resultat
     */
    public function removeResultat(\HopitalNumerique\AutodiagBundle\Entity\Resultat $resultat)
    {
        $this->resultats->removeElement($resultat);
    }

    /**
     * Set resultats
     *
     * @param \Doctrine\Common\Collections\Collection $resultats
     * @return Resultat
     */
    public function setResultats(array $resultats)
    {        
        $this->resultats = $resultats;
    
        return $this;
    }

    /**
     * Get resultats
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getResultats()
    {
        return $this->resultats;
    }
}
