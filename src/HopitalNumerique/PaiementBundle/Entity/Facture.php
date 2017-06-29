<?php

namespace HopitalNumerique\PaiementBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Gedmo\Mapping\Annotation as Gedmo;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\ModuleBundle\Entity\Inscription;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Facture.
 *
 * @ORM\Table(name="hn_facture")
 * @ORM\Entity(repositoryClass="HopitalNumerique\PaiementBundle\Repository\FactureRepository")
 * @Gedmo\Loggable
 */
class Facture
{
    /**
     * @var int
     *
     * @ORM\Column(name="fac_id", type="integer", options = {"comment" = "ID de la facture"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @GRID\Column(visible=false)
     *
     * @ORM\Column(name="fac_name", type="string", options = {"comment" = "Nom de la facture"}, nullable=true)
     * @Gedmo\Versioned
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
     * @GRID\Column(visible=false)
     *
     * @ORM\Column(name="fac_date_paiement", type="datetime", options = {"comment" = "Date de paiement de la facture"}, nullable=true)
     */
    private $datePaiement;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")
     *
     * @GRID\Column(field="user.lastname")
     * @GRID\Column(field="user.firstname")
     * @GRID\Column(field="user.email")
     * @GRID\Column(field="user.region.libelle")
     * @GRID\Column(field="user.organization.nom")
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
     * @var FactureAnnulee
     *
     * @ORM\OneToOne(targetEntity="FactureAnnulee", mappedBy="facture")
     * @GRID\Column(visible=false, filterable=true, field="factureAnnulee.facture.name")
     */
    protected $factureAnnulee;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionDemande", mappedBy="facture", cascade={"persist"})
     */
    private $interventions;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ModuleBundle\Entity\Inscription", mappedBy="facture", cascade={"persist"})
     */
    private $formations;

    /**
     * @var bool
     *
     * @ORM\Column(name="fac_annulee", type="boolean", options = {"comment" = "Est ce que la facture est annulee"})
     */
    private $annulee;

    /**
     * Initialisation de l'entitée (valeurs par défaut).
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->interventions = new ArrayCollection();
        $this->formations = new ArrayCollection();
        $this->payee = false;
        $this->annulee = false;
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
     * Get name.
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Facture
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return Facture
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation.
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set datePaiement.
     *
     * @param \DateTime $datePaiement
     *
     * @return Facture
     */
    public function setDatePaiement($datePaiement)
    {
        $this->datePaiement = $datePaiement;

        return $this;
    }

    /**
     * Get datePaiement.
     *
     * @return \DateTime
     */
    public function getDatePaiement()
    {
        return $this->datePaiement;
    }

    /**
     * Get user.
     *
     * @return User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return Facture
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get total.
     *
     * @return int $total
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set total.
     *
     * @param int $total
     *
     * @return Facture
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get payee.
     *
     * @return bool $payee
     */
    public function isPayee()
    {
        return $this->payee;
    }

    /**
     * Set payee.
     *
     * @param bool $payee
     *
     * @return Facture
     */
    public function setPayee($payee)
    {
        $this->payee = $payee;

        return $this;
    }

    /**
     * Get annulee.
     *
     * @return bool $annulee
     */
    public function isAnnulee()
    {
        return $this->annulee;
    }

    /**
     * Set annulee.
     *
     * @param bool $annulee
     *
     * @return Facture
     */
    public function setAnnulee($annulee)
    {
        $this->annulee = $annulee;

        return $this;
    }

    /**
     * Set factureAnnulee.
     *
     * @param FactureAnnulee $factureAnnulee
     *
     * @return Facture
     */
    public function setFactureAnnulee(FactureAnnulee $factureAnnulee = null)
    {
        $this->factureAnnulee = $factureAnnulee;

        return $this;
    }

    /**
     * Get factureAnnulee.
     *
     * @return FactureAnnulee
     */
    public function getFactureAnnulee()
    {
        return $this->factureAnnulee;
    }

    /**
     * Add intervention.
     *
     * @param InterventionDemande $intervention
     *
     * @return Facture
     */
    public function addIntervention(InterventionDemande $intervention)
    {
        $this->interventions[] = $intervention;

        return $this;
    }

    /**
     * Remove intervention.
     *
     * @param InterventionDemande $intervention
     */
    public function removeIntervention(InterventionDemande $intervention)
    {
        $this->interventions->removeElement($intervention);
        $intervention->setFacture(null);
    }

    /**
     * Remove all interventions.
     */
    public function removeInterventions()
    {
        foreach ($this->interventions as $intervention) {
            $this->removeIntervention($intervention);
        }
    }

    /**
     * Set interventions.
     *
     * @param Collection $interventions
     *
     * @return Facture
     */
    public function setInterventions(Collection $interventions)
    {
        $this->interventions = $interventions;

        return $this;
    }

    /**
     * Get interventions.
     *
     * @return Collection
     */
    public function getInterventions()
    {
        return $this->interventions;
    }

    /**
     * Add formation.
     *
     * @param Inscription $formation
     *
     * @return Facture
     */
    public function addFormation(Inscription $formation)
    {
        $this->formations[] = $formation;

        return $this;
    }

    /**
     * Remove formation.
     *
     * @param Inscription $formation
     */
    public function removeFormation(Inscription $formation)
    {
        $this->formations->removeElement($formation);
        $formation->setFacture(null);
    }

    /**
     * Remove all formations.
     */
    public function removeFormations()
    {
        foreach ($this->formations as $formation) {
            $this->removeFormation($formation);
        }
    }

    /**
     * Set formations.
     *
     * @param Collection $formations
     *
     * @return Facture
     */
    public function setFormations(Collection $formations)
    {
        $this->formations = $formations;

        return $this;
    }

    /**
     * Get formations.
     *
     * @return Collection
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

    /**
     * Retourne si la facture a été annulée.
     *
     * @return bool Si annulée
     */
    public function hasBeenCanceled()
    {
        return null !== $this->factureAnnulee;
    }
}
