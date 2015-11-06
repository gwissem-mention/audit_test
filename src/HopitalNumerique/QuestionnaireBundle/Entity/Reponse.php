<?php

namespace HopitalNumerique\QuestionnaireBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reponse
 *
 * @ORM\Table
 * (
 *     "hn_questionnaire_reponse",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="QUESTION_OCCURRENCE_USER", columns={"que_id", "usr_id", "occ_id"})}
 * )
 * @ORM\Entity(repositoryClass="HopitalNumerique\QuestionnaireBundle\Repository\ReponseRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Reponse
{
    /**
     * @var integer
     *
     * @ORM\Column(name="rep_id", type="integer", options = {"comment" = "ID de la réponse"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", inversedBy="reponses")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE", nullable=true)
     */
    private $user;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="reponses")
     * @ORM\JoinColumn(name="que_id", referencedColumnName="que_id", onDelete="CASCADE")
     */
    private $question;

    /**
     * @var string
     *
     * @ORM\Column(name="rep_reponse", type="text", options = {"comment" = "Contenu de la réponse"})
     */
    private $reponse;
    
    /**
     * @var \HopitalNumerique\QuestionnaireBundle\Entity\Occurrence
     * 
     * @ORM\ManyToOne(targetEntity="Occurrence", inversedBy="reponses")
     * @ORM\JoinColumn(name="occ_id", referencedColumnName="occ_id", onDelete="CASCADE")
     */
    private $occurrence;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_reference", referencedColumnName="ref_id")
     */
    protected $reference;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_questionnaire_reponse_reference",
     *      joinColumns={ @ORM\JoinColumn(name="rep_id", referencedColumnName="rep_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id")}
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $referenceMulitple;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\EtablissementBundle\Entity\Etablissement", cascade={"persist"})
     * @ORM\JoinColumn(name="eta_id", referencedColumnName="eta_id")
     */
    protected $etablissement;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\EtablissementBundle\Entity\Etablissement")
     * @ORM\JoinTable(name="hn_questionnaire_reponse_etablissement",
     *      joinColumns={ @ORM\JoinColumn(name="rep_id", referencedColumnName="rep_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="eta_id", referencedColumnName="eta_id")}
     * )
     * @ORM\OrderBy({"nom" = "ASC"})
     */
    protected $etablissementMulitple;

    /**
     * @var integer
     *
     * @ORM\Column(name="param_id", type="integer", nullable=true, options = {"comment" = "Éventuelle clef étrangère"})
     */
    private $paramId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="rep_date_update", type="datetime", nullable = true, options = {"comment" = "Date de mise a jour de la reponse"})
     */
    private $dateUpdate;

    public function __construct()
    {
        $this->dateUpdate = new \DateTime();
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
     * Set reponse
     *
     * @param string $reponse
     * @return Reponse
     */
    public function setReponse($reponse)
    {
        $this->reponse = $reponse;

        return $this;
    }

    /**
     * Get reponse
     *
     * @return string 
     */
    public function getReponse()
    {
        return $this->reponse;
    }

    /**
     * Set occurrence
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Occurrence $occurrence
     * @return Reponse
     */
    public function setOccurrence(\HopitalNumerique\QuestionnaireBundle\Entity\Occurrence $occurrence = null)
    {
        $this->occurrence = $occurrence;

        return $this;
    }

    /**
     * Get occurrence
     *
     * @return \HopitalNumerique\QuestionnaireBundle\Entity\Occurrence 
     */
    public function getOccurrence()
    {
        return $this->occurrence;
    }

    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return Reponse
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \HopitalNumerique\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set question
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Question $question
     * @return Reponse
     */
    public function setQuestion(\HopitalNumerique\QuestionnaireBundle\Entity\Question $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return \HopitalNumerique\QuestionnaireBundle\Entity\Question 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Get reference
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     */
    public function getReference()
    {
        return $this->reference;
    }
    
    /**
     * Set reference
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     */
    public function setReference($reference)
    {
        if($reference instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->reference = $reference;
        else
            $this->reference = null;
    }
    /**
     * Add referenceMulitple
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     * @return Objet
     */
    public function addReferenceMulitple(\HopitalNumerique\ReferenceBundle\Entity\Reference $reference)
    {
        $this->referenceMulitple[] = $reference;
    
        return $this;
    }
    
    /**
     * Remove referenceMulitple
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference$reference
     */
    public function removeReferenceMulitple(\HopitalNumerique\ReferenceBundle\Entity\Reference $reference)
    {
        $this->referenceMulitple->removeElement($reference);
    }
    
    /**
     * Set referenceMulitple
     *
     * @param \Doctrine\Common\Collections\Collection $referenceMulitple
     * @return Objet
     */
    public function setReferenceMulitple($referenceMulitple)
    {
        $this->referenceMulitple = $referenceMulitple;
    
        return $this;
    }
    
    /**
     * Get referenceMulitple
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferenceMulitple()
    {
        return $this->referenceMulitple;
    }

    /**
     * Get paramId
     *
     * @return integer $paramId
     */
    public function getParamId()
    {
        return $this->paramId;
    }
    
    /**
     * Set paramId
     *
     * @param integer $paramId
     */
    public function setParamId($paramId)
    {
        $this->paramId = $paramId;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     * @return Reponse
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime 
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function majDateUpdate()
    {
        $this->dateUpdate = new \DateTime();
    }
    
    
    // ---------------------Test de file---------------------------------
    public $file;

    /**
     * Set etablissement
     *
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissement
     * @return Reponse
     */
    public function setEtablissement(\HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissement = null)
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * Get etablissement
     *
     * @return \HopitalNumerique\EtablissementBundle\Entity\Etablissement 
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Add etablissementMulitple
     *
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissementMulitple
     * @return Reponse
     */
    public function addEtablissementMulitple(\HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissementMulitple)
    {
        $this->etablissementMulitple[] = $etablissementMulitple;

        return $this;
    }

    /**
     * Remove etablissementMulitple
     *
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissementMulitple
     */
    public function removeEtablissementMulitple(\HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissementMulitple)
    {
        $this->etablissementMulitple->removeElement($etablissementMulitple);
    }

    /**
     * Get etablissementMulitple
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEtablissementMulitple()
    {
        return $this->etablissementMulitple;
    }

    /**
     * Set etablissementMulitple
     *
     * @param \Doctrine\Common\Collections\Collection $etablissementMulitple
     * @return Objet
     */
    public function setEtablissementMulitple($etablissementMulitple)
    {
        $this->etablissementMulitple = $etablissementMulitple;
    
        return $this;
    }
}
