<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;

/**
 * Gabarit d'autodiag
 *
 * @ORM\Table(name="ad_model")
 * @ORM\Entity
 */
class Model
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Autodiag title
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * Autodiag instructions
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $instructions;

    /**
     * Authorize to show results if all questions aren't answered.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $partialResultsAuthorized;

    /**
     * Autorize synthesis
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $synthesisAuthorized;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\DomaineBundle\Entity\Domaine")
     * @ORM\JoinTable(
     *     name="ad_model_domain",
     *     joinColumns={@ORM\JoinColumn(name="model_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="domaine_id", referencedColumnName="dom_id")}
     * )
     */
    private $domaines;

    /**
     * @var Questionnaire
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire")
     * @ORM\JoinColumn(name="questionnaire_id", referencedColumnName="qst_id", onDelete="SET NULL")
     */
    private $questionnaire;


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
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get instructions
     *
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * Set instructions
     *
     * @param string $instructions
     * @return $this
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;

        return $this;
    }

    /**
     * Is show results if all questions aren't answered authorized
     *
     * @return boolean
     */
    public function isPartialResultsAuthorized()
    {
        return $this->partialResultsAuthorized;
    }

    /**
     * Set partial results authorization
     *
     * @param boolean $partialResultsAuthorized
     * @return $this
     */
    public function setPartialResultsAuthorized($partialResultsAuthorized)
    {
        $this->partialResultsAuthorized = $partialResultsAuthorized;

        return $this;
    }

    /**
     * Is synthesis authorized
     *
     * @return boolean
     */
    public function isSynthesisAuthorized()
    {
        return $this->synthesisAuthorized;
    }

    /**
     * Set synthesis authorization
     *
     * @param boolean $synthesisAuthorized
     * @return $this
     */
    public function setSynthesisAuthorized($synthesisAuthorized)
    {
        $this->synthesisAuthorized = $synthesisAuthorized;

        return $this;
    }

    /**
     * Get Domaine
     *
     * @return Collection
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /**
     * Add domaine
     *
     * @param Domaine $domaine
     * @return $this
     */
    public function addDomaine(Domaine $domaine)
    {
        $this->domaines->add($domaine);

        return $this;
    }

    /**
     * Remove domaine
     *
     * @param Domaine $domaine
     * @return $this
     */
    public function removeDomaine(Domaine $domaine)
    {
        $this->domaines->removeElement($domaine);

        return $this;
    }

    /**
     * Get prior questionnaire
     *
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * Set prior questionnaire
     *
     * @param Questionnaire $questionnaire
     * @return $this
     */
    public function setQuestionnaire($questionnaire = null)
    {
        $this->questionnaire = $questionnaire;
        return $this;
    }
}
