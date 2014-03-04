<?php

namespace HopitalNumerique\InterventionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entité d'une demande d'intervention.
 *
 * @ORM\Table(name="hn_intervention_demande", indexes={@ORM\Index(name="fk_hn_intervention_core_user", columns={"referent_id"}), @ORM\Index(name="fk_hn_intervention_demande_core_user1", columns={"ambassadeur_id"}), @ORM\Index(name="fk_hn_intervention_demande_core_user2", columns={"cmsi_id"}), @ORM\Index(name="fk_hn_intervention_demande_core_user3", columns={"directeur_id"}), @ORM\Index(name="fk_hn_intervention_demande_hn_reference1", columns={"ref_intervention_type_id"}), @ORM\Index(name="fk_hn_intervention_demande_hn_reference2", columns={"ref_intervention_etat_id"}), @ORM\Index(name="fk_hn_intervention_demande_hn_intervention_initiateur1", columns={"intervinit_id"}), @ORM\Index(name="fk_hn_intervention_demande_hn_reference3", columns={"ref_evaluation_etat_id"}), @ORM\Index(name="fk_hn_intervention_demande_hn_reference4", columns={"ref_remboursement_etat_id"})})
 * @ORM\Entity
 */
class InterventionDemande
{
    /**
     * @var integer
     *
     * @ORM\Column(name="interv_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="interv_date_creation", type="datetime", nullable=false)
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="interv_cmsi_date_choix", type="datetime", nullable=true)
     */
    private $cmsiDateChoix;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="interv_ambassadeur_date_choix", type="datetime", nullable=true)
     */
    private $ambassadeurDateChoix;

    /**
     * @var string
     *
     * @ORM\Column(name="interv_autres_etablissements", type="text", nullable=true)
     */
    private $autresEtablissements;

    /**
     * @var string
     *
     * @ORM\Column(name="interv_description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="interv_difficulte_description", type="text", nullable=true)
     */
    private $difficulteDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="interv_champ_libre", type="text", nullable=true)
     */
    private $champLibre;

    /**
     * @var string
     *
     * @ORM\Column(name="interv_rdv_informations", type="text", nullable=true)
     */
    private $rdvInformations;

    /**
     * @var string
     *
     * @ORM\Column(name="interv_refus_message", type="text", nullable=true)
     */
    private $refusMessage;

    /**
     * @var \CoreUser
     *
     * @ORM\ManyToOne(targetEntity="CoreUser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="referent_id", referencedColumnName="usr_id")
     * })
     */
    private $referent;

    /**
     * @var \CoreUser
     *
     * @ORM\ManyToOne(targetEntity="CoreUser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ambassadeur_id", referencedColumnName="usr_id")
     * })
     */
    private $ambassadeur;
    
    /**
     * @var \CoreUser
     *
     * @ORM\ManyToOne(targetEntity="CoreUser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cmsi_id", referencedColumnName="usr_id")
     * })
     */
    private $cmsi;

    /**
     * @var \CoreUser
     *
     * @ORM\ManyToOne(targetEntity="CoreUser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="directeur_id", referencedColumnName="usr_id")
     * })
     */
    private $directeur;

    /**
     * @var \InterventionInitiateur
     *
     * @ORM\ManyToOne(targetEntity="InterventionInitiateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="intervinit_id", referencedColumnName="intervinit_id")
     * })
     */
    private $interventionInitiateur;

    /**
     * @var \HnReference
     *
     * @ORM\ManyToOne(targetEntity="HnReference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_intervention_type_id", referencedColumnName="ref_id")
     * })
     */
    private $interventionType;

    /**
     * @var \HnReference
     *
     * @ORM\ManyToOne(targetEntity="HnReference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_intervention_etat_id", referencedColumnName="ref_id")
     * })
     */
    private $interventionEtat;

    /**
     * @var \HnReference
     *
     * @ORM\ManyToOne(targetEntity="HnReference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_evaluation_etat_id", referencedColumnName="ref_id")
     * })
     */
    private $evaluationEtat;

    /**
     * @var \HnReference
     *
     * @ORM\ManyToOne(targetEntity="HnReference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_remboursement_etat_id", referencedColumnName="ref_id")
     * })
     */
    private $remboursementEtat;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="CoreUser", inversedBy="interv")
     * @ORM\JoinTable(name="hn_intervention_ambassadeur_historique",
     *   joinColumns={
     *     @ORM\JoinColumn(name="interv_id", referencedColumnName="interv_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="ambassadeur_id", referencedColumnName="usr_id")
     *   }
     * )
     */
    private $ambassadeurs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="HnEtablissement", inversedBy="interv")
     * @ORM\JoinTable(name="hn_intervention_etablissement_rattache",
     *   joinColumns={
     *     @ORM\JoinColumn(name="interv_id", referencedColumnName="interv_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="eta_id", referencedColumnName="eta_id")
     *   }
     * )
     */
    private $etablissements;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="HnObjet", inversedBy="interv")
     * @ORM\JoinTable(name="hn_intervention_objet",
     *   joinColumns={
     *     @ORM\JoinColumn(name="interv_id", referencedColumnName="interv_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id")
     *   }
     * )
     */
    private $objets;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ambassadeur = new \Doctrine\Common\Collections\ArrayCollection();
        $this->eta = new \Doctrine\Common\Collections\ArrayCollection();
        $this->obj = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
