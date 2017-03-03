<?php

namespace HopitalNumerique\QuestionnaireBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Question.
 *
 * @ORM\Table("hn_questionnaire_question")
 * @ORM\Entity(repositoryClass="HopitalNumerique\QuestionnaireBundle\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @var int
     *
     * @ORM\Column(name="que_id", type="integer", options = {"comment" = "ID de la question"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="que_alias", type="string", length=60, options = {"comment" = "Alias de la question"})
     */
    protected $alias;

    /**
     * @var string
     *
     * @ORM\Column(name="que_libelle", type="string", length=255, options = {"comment" = "Libellé de la question"})
     */
    protected $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="que_reference_param_tri", type="string", length=50, nullable=true, options = {"comment" = "Code de la référence si le type est une entitée"})
     */
    protected $referenceParamTri;

    /**
     * @var bool
     *
     * @ORM\Column(name="que_obligatoire", type="boolean", options = {"comment" = "La question est-elle obligatoire ?"})
     */
    protected $obligatoire;

    /**
     * @var string
     *
     * @ORM\Column(name="que_verifJS", type="string", length=255, nullable=true, options = {"comment" = "Vérification JavaScript"})
     */
    protected $verifJS;

    /**
     * @var int
     *
     * @ORM\Column(name="que_ordre", type="integer", options = {"comment" = "Ordre de la question"})
     */
    protected $ordre;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Questionnaire", inversedBy="questions")
     * @ORM\JoinColumn(name="qst_id", referencedColumnName="qst_id")
     */
    protected $questionnaire;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\QuestionnaireBundle\Entity\TypeQuestion", cascade={"persist"})
     * @ORM\JoinColumn(name="typ_question", referencedColumnName="typ_id")
     */
    protected $typeQuestion;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\QuestionnaireBundle\Entity\Reponse", mappedBy="question", cascade={"persist", "remove" })
     */
    protected $reponses;

    /**
     * @var string
     *
     * @ORM\Column(name="que_commentaire", type="text", nullable=true, options = {"comment" = "Commentaire pour le type commentaire"})
     */
    protected $commentaire;

    public function __construct()
    {
        $this->ordre = 0;
        $this->alias = '';
        $this->libelle = '';
        $this->obligatoire = true;
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
     * Set alias.
     *
     * @param string $alias
     *
     * @return Question
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return Question
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set obligatoire.
     *
     * @param bool $obligatoire
     *
     * @return Question
     */
    public function setObligatoire($obligatoire)
    {
        $this->obligatoire = $obligatoire;

        return $this;
    }

    /**
     * Get obligatoire.
     *
     * @return bool
     */
    public function getObligatoire()
    {
        return $this->obligatoire;
    }

    /**
     * Set verifJS.
     *
     * @param string $verifJS
     *
     * @return Question
     */
    public function setVerifJS($verifJS)
    {
        $this->verifJS = $verifJS;

        return $this;
    }

    /**
     * Get verifJS.
     *
     * @return string
     */
    public function getVerifJS()
    {
        return $this->verifJS;
    }

    /**
     * Set ordre.
     *
     * @param int $ordre
     *
     * @return Question
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre.
     *
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Get questionnaire.
     *
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * Set ordre.
     *
     * @param Questionnaire $questionnaire
     *
     * @return Question this
     */
    public function setQuestionnaire(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    /**
     * Get typeQuestion.
     *
     * @return \HopitalNumerique\QuestionnaireBundle\Entity\TypeQuestion $typeQuestion
     */
    public function getTypeQuestion()
    {
        return $this->typeQuestion;
    }

    /**
     * Set civilite.
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\TypeQuestion $typeQuestion
     */
    public function setTypeQuestion($typeQuestion)
    {
        if ($typeQuestion instanceof \HopitalNumerique\QuestionnaireBundle\Entity\TypeQuestion) {
            $this->typeQuestion = $typeQuestion;
        } else {
            $this->typeQuestion = null;
        }
    }

    /**
     * Add reponses.
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses
     *
     * @return Question
     */
    public function addReponse(\HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses)
    {
        $this->reponses[] = $reponses;

        return $this;
    }

    /**
     * Remove reponses.
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses
     */
    public function removeReponse(\HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses)
    {
        $this->reponses->removeElement($reponses);
    }

    /**
     * Get reponses.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReponses()
    {
        return $this->reponses;
    }

    /**
     * Set referenceParamTri.
     *
     * @param string $referenceParamTri
     *
     * @return Question
     */
    public function setReferenceParamTri($referenceParamTri)
    {
        $this->referenceParamTri = $referenceParamTri;

        return $this;
    }

    /**
     * Get referenceParamTri.
     *
     * @return string
     */
    public function getReferenceParamTri()
    {
        return $this->referenceParamTri;
    }

    /**
     * Set commentaire.
     *
     * @param string $commentaire
     *
     * @return Question
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire.
     *
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }
}
