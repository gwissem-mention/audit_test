<?php

namespace HopitalNumerique\QuestionnaireBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nodevo\ToolsBundle\Tools\Chaine;

/**
 * Questionnaire
 *
 * @ORM\Table("hn_questionnaire_questionnaire")
 * @ORM\Entity(repositoryClass="HopitalNumerique\QuestionnaireBundle\Repository\QuestionnaireRepository")
 */
class Questionnaire
{
    /**
     * @var integer
     *
     * @ORM\Column(name="qst_id", type="integer", options = {"comment" = "ID du questionnaire"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="qst_nom", type="string", length=255, options = {"comment" = "Nom du questionnaire"})
     */
    private $nom;

    /**
     * @var boolean
     *
     * @ORM\Column(name="qst_lock", type="boolean", options = {"comment" = "Verrouillage du questionnaire ?"})
     */
    private $lock;

    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="questionnaire", cascade={"persist", "remove" })
     * @ORM\OrderBy({"ordre" = "ASC"})
     */
    private $questions;
    
    /**
     * @ORM\OneToMany(targetEntity="HopitalNumerique\UserBundle\Entity\RefusCandidature", mappedBy="user", cascade={"persist", "remove" })
     */
    protected $refusCandidature;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->questions    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lock = false;
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
     * Set nom
     *
     * @param string $nom
     * @return Questionnaire
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }
    
    /**
     * Get nom minifié
     *
     * @return string
     */
    public function getNomMinifie()
    {
        $nom = new Chaine($this->nom);
        
        return $nom->minifie();
    }

    /**
     * Get lock
     *
     * @return boolean $lock
     */
    public function getLock()
    {
        return $this->lock;
    }
    
    /**
     * Set lock
     *
     * @param boolean $lock
     */
    public function setLock($lock)
    {
        $this->lock = $lock;
    }
    

    /**
     * Add questions
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Question $questions
     * @return Questionnaire
     */
    public function addQuestion(\HopitalNumerique\QuestionnaireBundle\Entity\Question $questions)
    {
        $this->questions[] = $questions;
    
        return $this;
    }
    
    /**
     * Remove questions
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Question $questions
     */
    public function removeQuestion(\HopitalNumerique\QuestionnaireBundle\Entity\Question $questions)
    {
        $this->questions->removeElement($questions);
    }
    
    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }
}
