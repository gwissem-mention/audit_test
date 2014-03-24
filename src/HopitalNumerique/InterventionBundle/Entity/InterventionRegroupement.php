<?php
namespace HopitalNumerique\InterventionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entité du regroupement d'interventions.
 *
 * @ORM\Table(name="hn_intervention_regroupement", indexes={@ORM\Index(name="fk_hn_intervention_has_hn_intervention_hn_intervention2", columns={"interv_regroupee_id"}), @ORM\Index(name="fk_hn_intervention_has_hn_intervention_hn_intervention1", columns={"interv_principale_id"}), @ORM\Index(name="fk_hn_intervention_regroupement_hn_intervention_regroupement_1", columns={"intervregtyp_id"})})
 * @ORM\Entity(repositoryClass="HopitalNumerique\InterventionBundle\Repository\InterventionRegroupementRepository")
 */
class InterventionRegroupement
{
    /**
     * @var \InterventionRegroupementType
     *
     * @ORM\ManyToOne(targetEntity="InterventionRegroupementType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="intervregtyp_id", referencedColumnName="intervregtyp_id")
     * })
     */
    private $interventionRegroupementType;

    /**
     * @var \InterventionDemande
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="InterventionDemande")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="interv_principale_id", referencedColumnName="interv_id")
     * })
     */
    private $interventionDemandePrincipale;

    /**
     * @var \InterventionDemande
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="InterventionDemande")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="interv_regroupee_id", referencedColumnName="interv_id")
     * })
     */
    private $interventionDemandeRegroupee;

    /**
     * Set interventionRegroupementType
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionRegroupementType $interventionRegroupementType
     * @return InterventionRegroupement
     */
    public function setInterventionRegroupementType(
            \HopitalNumerique\InterventionBundle\Entity\InterventionRegroupementType $interventionRegroupementType = null)
    {
        $this->interventionRegroupementType = $interventionRegroupementType;

        return $this;
    }

    /**
     * Get interventionRegroupementType
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionRegroupementType 
     */
    public function getInterventionRegroupementType()
    {
        return $this->interventionRegroupementType;
    }

    /**
     * Set interventionDemandePrincipale
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemandePrincipale
     * @return InterventionRegroupement
     */
    public function setInterventionDemandePrincipale(
            \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemandePrincipale)
    {
        $this->interventionDemandePrincipale = $interventionDemandePrincipale;

        return $this;
    }

    /**
     * Get interventionDemandePrincipale
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande 
     */
    public function getInterventionDemandePrincipale()
    {
        return $this->interventionDemandePrincipale;
    }

    /**
     * Set interventionDemandeRegroupee
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemandeRegroupee
     * @return InterventionRegroupement
     */
    public function setInterventionDemandeRegroupee(
            \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemandeRegroupee)
    {
        $this->interventionDemandeRegroupee = $interventionDemandeRegroupee;

        return $this;
    }

    /**
     * Get interventionDemandeRegroupee
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande 
     */
    public function getInterventionDemandeRegroupee()
    {
        return $this->interventionDemandeRegroupee;
    }
}
