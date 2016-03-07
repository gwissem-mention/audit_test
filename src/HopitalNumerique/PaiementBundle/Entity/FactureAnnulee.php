<?php
namespace HopitalNumerique\PaiementBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Facture annulée.
 *
 * @ORM\Entity()
 * @ORM\Table(name="hn_facture_annulee")
 */
class FactureAnnulee
{
    /**
     * @var integer
     *
     * @ORM\Column(name="facan_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \HopitalNumerique\PaiementBundle\Entity\Facture
     *
     * @ORM\OneToOne(targetEntity="Facture", inversedBy="factureAnnulee")
     * @ORM\JoinColumn(name="fac_id", referencedColumnName="fac_id", nullable=false)
     */
    private $facture;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\InterventionBundle\Entity\InterventionDemande", inversedBy="facturesAnnulees")
     * @ORM\JoinTable
     * (
     *  name="hn_facture_annulee_intervention",
     *  joinColumns={@ORM\JoinColumn(name="facan_id", referencedColumnName="facan_id", onDelete="CASCADE")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="interv_id", referencedColumnName="interv_id", onDelete="CASCADE")}
     * )
     */
    private $interventions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\ModuleBundle\Entity\Inscription", inversedBy="facturesAnnulees")
     * @ORM\JoinTable
     * (
     *  name="hn_facture_annulee_inscription",
     *  joinColumns={@ORM\JoinColumn(name="facan_id", referencedColumnName="facan_id", onDelete="CASCADE")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="insc_id", referencedColumnName="insc_id", onDelete="CASCADE")}
     * )
     */
    private $formations;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->interventions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->formations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set facture
     *
     * @param \HopitalNumerique\PaiementBundle\Entity\Facture $facture
     *
     * @return FactureAnnulee
     */
    public function setFacture(\HopitalNumerique\PaiementBundle\Entity\Facture $facture)
    {
        $this->facture = $facture;

        return $this;
    }

    /**
     * Get facture
     *
     * @return \HopitalNumerique\PaiementBundle\Entity\Facture
     */
    public function getFacture()
    {
        return $this->facture;
    }

    /**
     * Add intervention
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $intervention
     *
     * @return FactureAnnulee
     */
    public function addIntervention(\HopitalNumerique\InterventionBundle\Entity\InterventionDemande $intervention)
    {
        $this->interventions[] = $intervention;

        return $this;
    }

    /**
     * Set interventions
     *
     * @param \Doctrine\Common\Collections\Collection $interventions
     *
     * @return FactureAnnulee
     */
    public function setInterventions(Collection $interventions)
    {
        $this->interventions = $interventions;

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
     *
     * @return FactureAnnulee
     */
    public function addFormation(\HopitalNumerique\ModuleBundle\Entity\Inscription $formation)
    {
        $this->formations[] = $formation;

        return $this;
    }

    /**
     * Set formations
     *
     * @param \Doctrine\Common\Collections\Collection $formations
     *
     * @return FactureAnnulee
     */
    public function setFormations(Collection $formations)
    {
        $this->formations = $formations;

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
     * Get formations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFormations()
    {
        return $this->formations;
    }
}
