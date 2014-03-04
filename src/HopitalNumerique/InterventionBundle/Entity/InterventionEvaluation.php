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
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_attente_id", referencedColumnName="ref_id")
     * })
     */
    private $attente;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_presentation_contexte_id", referencedColumnName="ref_id")
     * })
     */
    private $presentationContexte;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_utilite_id", referencedColumnName="ref_id")
     * })
     */
    private $utilite;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_utilisation_prealable_id", referencedColumnName="ref_id")
     * })
     */
    private $utilisationPrealable;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_modalites_pratiques_id", referencedColumnName="ref_id")
     * })
     */
    private $modalitesPratiques;



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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return InterventionEvaluation
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
     * Set presentationSupplement
     *
     * @param string $presentationSupplement
     * @return InterventionEvaluation
     */
    public function setPresentationSupplement($presentationSupplement)
    {
        $this->presentationSupplement = $presentationSupplement;

        return $this;
    }

    /**
     * Get presentationSupplement
     *
     * @return string 
     */
    public function getPresentationSupplement()
    {
        return $this->presentationSupplement;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return InterventionEvaluation
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string 
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set interventionDemande
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande
     * @return InterventionEvaluation
     */
    public function setInterventionDemande(\HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande = null)
    {
        $this->interventionDemande = $interventionDemande;

        return $this;
    }

    /**
     * Get interventionDemande
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande 
     */
    public function getInterventionDemande()
    {
        return $this->interventionDemande;
    }

    /**
     * Set attente
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $attente
     * @return InterventionEvaluation
     */
    public function setAttente(\HopitalNumerique\ReferenceBundle\Entity\Reference $attente = null)
    {
        $this->attente = $attente;

        return $this;
    }

    /**
     * Get attente
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getAttente()
    {
        return $this->attente;
    }

    /**
     * Set presentationContexte
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $presentationContexte
     * @return InterventionEvaluation
     */
    public function setPresentationContexte(\HopitalNumerique\ReferenceBundle\Entity\Reference $presentationContexte = null)
    {
        $this->presentationContexte = $presentationContexte;

        return $this;
    }

    /**
     * Get presentationContexte
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getPresentationContexte()
    {
        return $this->presentationContexte;
    }

    /**
     * Set utilite
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $utilite
     * @return InterventionEvaluation
     */
    public function setUtilite(\HopitalNumerique\ReferenceBundle\Entity\Reference $utilite = null)
    {
        $this->utilite = $utilite;

        return $this;
    }

    /**
     * Get utilite
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getUtilite()
    {
        return $this->utilite;
    }

    /**
     * Set utilisationPrealable
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $utilisationPrealable
     * @return InterventionEvaluation
     */
    public function setUtilisationPrealable(\HopitalNumerique\ReferenceBundle\Entity\Reference $utilisationPrealable = null)
    {
        $this->utilisationPrealable = $utilisationPrealable;

        return $this;
    }

    /**
     * Get utilisationPrealable
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getUtilisationPrealable()
    {
        return $this->utilisationPrealable;
    }

    /**
     * Set modalitesPratiques
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $modalitesPratiques
     * @return InterventionEvaluation
     */
    public function setModalitesPratiques(\HopitalNumerique\ReferenceBundle\Entity\Reference $modalitesPratiques = null)
    {
        $this->modalitesPratiques = $modalitesPratiques;

        return $this;
    }

    /**
     * Get modalitesPratiques
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getModalitesPratiques()
    {
        return $this->modalitesPratiques;
    }
}
