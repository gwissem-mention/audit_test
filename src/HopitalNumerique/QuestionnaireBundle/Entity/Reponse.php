<?php

namespace HopitalNumerique\QuestionnaireBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Reponse.
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
     * @var int
     *
     * @ORM\Column(name="rep_id", type="integer", options = {"comment" = "ID de la réponse"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", inversedBy="reponses")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE", nullable=true)
     */
    private $user;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="reponses")
     * @ORM\JoinColumn(name="que_id", referencedColumnName="que_id", onDelete="CASCADE")
     */
    private $question;

    /**
     * @var string
     *
     * @ORM\Column(name="rep_reponse", type="text", nullable=true, options = {"comment" = "Contenu de la réponse"})
     */
    private $reponse;

    /**
     * @var Occurrence
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
     * @var int
     *
     * @ORM\Column(name="param_id", type="integer", nullable=true, options = {"comment" = "Éventuelle clef étrangère"})
     */
    private $paramId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="rep_date_creation", type="datetime", nullable=true, options={"comment"="Date de création de la réponse"})
     */
    private $dateCreation;

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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set reponse.
     *
     * @param string $reponse
     *
     * @return Reponse
     */
    public function setReponse($reponse)
    {
        $this->reponse = $reponse;

        return $this;
    }

    /**
     * Get reponse.
     *
     * @return string
     */
    public function getReponse()
    {
        return $this->reponse;
    }

    /**
     * Set occurrence.
     *
     * @param Occurrence $occurrence
     *
     * @return Reponse
     */
    public function setOccurrence(Occurrence $occurrence = null)
    {
        $this->occurrence = $occurrence;

        return $this;
    }

    /**
     * Get occurrence.
     *
     * @return Occurrence
     */
    public function getOccurrence()
    {
        return $this->occurrence;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return Reponse
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set question.
     *
     * @param Question $question
     *
     * @return Reponse
     */
    public function setQuestion(Question $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question.
     *
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Get reference.
     *
     * @return Reference $reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set reference.
     *
     * @param Reference $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference instanceof Reference ? $reference : null;
    }

    /**
     * Add referenceMulitple.
     *
     * @param Reference $reference
     *
     * @return Reponse
     */
    public function addReferenceMulitple(Reference $reference)
    {
        $this->referenceMulitple[] = $reference;

        return $this;
    }

    /**
     * Remove referenceMulitple.
     *
     * @param Reference$reference
     */
    public function removeReferenceMulitple(Reference $reference)
    {
        $this->referenceMulitple->removeElement($reference);
    }

    /**
     * Set referenceMulitple.
     *
     * @param Collection $referenceMulitple
     *
     * @return Reponse
     */
    public function setReferenceMulitple($referenceMulitple)
    {
        $this->referenceMulitple = $referenceMulitple;

        return $this;
    }

    /**
     * Get referenceMulitple.
     *
     * @return Collection
     */
    public function getReferenceMulitple()
    {
        return $this->referenceMulitple;
    }

    /**
     * Get paramId.
     *
     * @return int $paramId
     */
    public function getParamId()
    {
        return $this->paramId;
    }

    /**
     * Set paramId.
     *
     * @param int $paramId
     */
    public function setParamId($paramId)
    {
        $this->paramId = $paramId;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return Reponse
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
     * Set dateUpdate.
     *
     * @param \DateTime $dateUpdate
     *
     * @return Reponse
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate.
     *
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->dateCreation = new \DateTime();
        $this->preUpdate();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->dateUpdate = new \DateTime();
    }

    // ---------------------Test de file---------------------------------
    public $file;

    /**
     * Set etablissement.
     *
     * @param Etablissement $etablissement
     *
     * @return Reponse
     */
    public function setEtablissement(Etablissement $etablissement = null)
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * Get etablissement.
     *
     * @return Etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Add etablissementMulitple.
     *
     * @param Etablissement $etablissementMulitple
     *
     * @return Reponse
     */
    public function addEtablissementMulitple(Etablissement $etablissementMulitple)
    {
        $this->etablissementMulitple[] = $etablissementMulitple;

        return $this;
    }

    /**
     * Remove etablissementMulitple.
     *
     * @param Etablissement $etablissementMulitple
     */
    public function removeEtablissementMulitple(Etablissement $etablissementMulitple)
    {
        $this->etablissementMulitple->removeElement($etablissementMulitple);
    }

    /**
     * Get etablissementMulitple.
     *
     * @return Collection
     */
    public function getEtablissementMulitple()
    {
        return $this->etablissementMulitple;
    }

    /**
     * Set etablissementMulitple.
     *
     * @param Collection $etablissementMulitple
     *
     * @return Reponse
     */
    public function setEtablissementMulitple($etablissementMulitple)
    {
        $this->etablissementMulitple = $etablissementMulitple;

        return $this;
    }
}
