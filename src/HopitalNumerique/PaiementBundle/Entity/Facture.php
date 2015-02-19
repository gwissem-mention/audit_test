<?php

namespace HopitalNumerique\PaiementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Facture
 *
 * @ORM\Table(name="hn_facture")
 * @ORM\Entity(repositoryClass="HopitalNumerique\PaiementBundle\Repository\FactureRepository")
 */
class Facture
{
    /**
     * @var integer
     *
     * @ORM\Column(name="fac_id", type="integer", options = {"comment" = "ID de la facture"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fac_name", type="string", options = {"comment" = "Nom de la facture"}, nullable=true)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fac_date_creation", type="datetime", options = {"comment" = "Date de création de la facture"})
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fac_date_paiement", type="datetime", options = {"comment" = "Date de paiement de la facture"}, nullable=true)
     */
    private $datePaiement;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")
     *
     * @GRID\Column(field="user.nom")
     * @GRID\Column(field="user.prenom")
     * @GRID\Column(field="user.email")
     * @GRID\Column(field="user.region.libelle")
     * @GRID\Column(field="user.etablissementRattachementSante.nom")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="fac_total", type="smallint", options = {"comment" = "Total de la facture"}, nullable=true)
     */
    private $total;

    /**
     * @var string
     *
     * @ORM\Column(name="fac_payee", type="boolean", options = {"comment" = "Est ce que la facture est payee"})
     */
    private $payee;
    
    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionDemande", mappedBy="facture", cascade={"persist"})
     */
    private $interventions;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ModuleBundle\Entity\Inscription", mappedBy="facture", cascade={"persist"})
     */
    private $formations;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->dateCreation  = new \DateTime();
        $this->interventions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->formations    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->payee         = false;
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Facture
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set datePaiement
     *
     * @param \DateTime $datePaiement
     * @return Facture
     */
    public function setDatePaiement($datePaiement)
    {
        $this->datePaiement = $datePaiement;

        return $this;
    }

    /**
     * Get datePaiement
     *
     * @return \DateTime 
     */
    public function getDatePaiement()
    {
        return $this->datePaiement;
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
     * Get total
     *
     * @return integer $total
     */
    public function getTotal()
    {
        return $this->total;
    }
    
    /**
     * Set total
     *
     * @param integer $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * Get payee
     *
     * @return boolean $payee
     */
    public function isPayee()
    {
        return $this->payee;
    }
    
    /**
     * Set payee
     *
     * @param boolean $payee
     */
    public function setPayee($payee)
    {
        $this->payee = $payee;
        return $this;
    }
    
    /**
     * Add intervention
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $intervention
     * @return Facture
     */
    public function addIntervention(\HopitalNumerique\InterventionBundle\Entity\InterventionDemande $intervention)
    {
        $this->interventions[] = $intervention;
    
        return $this;
    }

    /**
     * Remove intervention
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $intervention
     */
    public function removeIntervention(\HopitalNumerique\InterventionBundle\Entity\InterventionDemande $intervention)
    {
        $this->interventions->removeElement($intervention);
    }

    /**
     * Set interventions
     *
     * @param \Doctrine\Common\Collections\Collection $interventions
     * @return Facture
     */
    public function setInterventions(\Doctrine\Common\Collections\Collection $interventions)
    {
        $this->interventions = $interventions;
    
        return $this;
    }

    /**
     * Get interventions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInterventions()
    {
        return $this->interventions;
    }

    /**
     * Add formation
     *
     * @param \HopitalNumerique\ModuleBundle\Entity\Inscription $formation
     * @return Facture
     */
    public function addFormation(\HopitalNumerique\ModuleBundle\Entity\Inscription $formation)
    {
        $this->formations[] = $formation;
    
        return $this;
    }

    /**
     * Remove formation
     *
     * @param \HopitalNumerique\ModuleBundle\Entity\Inscription $formation
     */
    public function removeFormation(\HopitalNumerique\ModuleBundle\Entity\Inscription $formation)
    {
        $this->formations->removeElement($formation);
    }

    /**
     * Set formations
     *
     * @param \Doctrine\Common\Collections\Collection $formations
     * @return Facture
     */
    public function setFormations(\Doctrine\Common\Collections\Collection $formations)
    {
        $this->formations = $formations;
    
        return $this;
    }

    /**
     * Get formations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFormations()
    {
        return $this->formations;
    }

    /**
     * toString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
