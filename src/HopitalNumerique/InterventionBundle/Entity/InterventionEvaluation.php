<?php

namespace HopitalNumerique\InterventionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entité d'une évaluation d'une intervention.
 *
 * @ORM\Table(name="hn_intervention_evaluation", indexes={@ORM\Index(name="fk_hn_intervention_evaluation_hn_intervention_demande1", columns={"interv_id"}), @ORM\Index(name="fk_hn_intervention_evaluation_hn_reference1", columns={"ref_attente_id"}), @ORM\Index(name="fk_hn_intervention_evaluation_hn_reference2", columns={"ref_presentation_contexte_id"}), @ORM\Index(name="fk_hn_intervention_evaluation_hn_reference3", columns={"ref_utilite_id"}), @ORM\Index(name="fk_hn_intervention_evaluation_hn_reference4", columns={"ref_utilisation_prealable_id"}), @ORM\Index(name="fk_hn_intervention_evaluation_hn_reference5", columns={"ref_modalites_pratiques_id"})})
 * @ORM\Entity
 */
class InterventionEvaluation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="interveval_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="interveval_date_creation", type="datetime", nullable=false)
     */
    private $dateCreation;

    /**
     * @var string
     *
     * @ORM\Column(name="interveval_presentation_supplement", type="text", nullable=true)
     */
    private $presentationSupplement;

    /**
     * @var string
     *
     * @ORM\Column(name="interveval_commentaire", type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @var \InterventionDemande
     *
     * @ORM\ManyToOne(targetEntity="InterventionDemande")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="interv_id", referencedColumnName="interv_id")
     * })
     */
    private $interventionDemande;

    /**
     * @var \HnReference
     *
     * @ORM\ManyToOne(targetEntity="HnReference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_attente_id", referencedColumnName="ref_id")
     * })
     */
    private $attente;

    /**
     * @var \HnReference
     *
     * @ORM\ManyToOne(targetEntity="HnReference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_presentation_contexte_id", referencedColumnName="ref_id")
     * })
     */
    private $presentationContexte;

    /**
     * @var \HnReference
     *
     * @ORM\ManyToOne(targetEntity="HnReference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_utilite_id", referencedColumnName="ref_id")
     * })
     */
    private $utilite;

    /**
     * @var \HnReference
     *
     * @ORM\ManyToOne(targetEntity="HnReference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_utilisation_prealable_id", referencedColumnName="ref_id")
     * })
     */
    private $utilisationPrealable;

    /**
     * @var \HnReference
     *
     * @ORM\ManyToOne(targetEntity="HnReference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_modalites_pratiques_id", referencedColumnName="ref_id")
     * })
     */
    private $modalitesPratiques;


}
