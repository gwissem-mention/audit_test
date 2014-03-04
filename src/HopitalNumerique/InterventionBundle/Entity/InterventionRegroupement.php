<?php

namespace HopitalNumerique\InterventionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entité du regroupement d'interventions.
 *
 * @ORM\Table(name="hn_intervention_regroupement", indexes={@ORM\Index(name="fk_hn_intervention_has_hn_intervention_hn_intervention2", columns={"interv_regroupee_id"}), @ORM\Index(name="fk_hn_intervention_has_hn_intervention_hn_intervention1", columns={"interv_principale_id"}), @ORM\Index(name="fk_hn_intervention_regroupement_hn_intervention_regroupement_1", columns={"intervregtyp_id"})})
 * @ORM\Entity
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


}
