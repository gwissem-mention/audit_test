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
     * @var string
     *
     * @ORM\Column(name="qst_lien", type="string", length=255, nullable=true, options = {"comment" = "Lien de redirection après validation du questionnaire"})
     */
    protected $lien;
    
    /**
     * @var boolean
     * 
     * @ORM\Column(name="qst_occurrence_multiple", type="boolean", nullable=false, options={"default"=false, "comment"="Indique si le questionnaire peut être répondu plusieurs fois"})
     */
    private $occurrenceMultiple;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Occurrence", mappedBy="questionnaire")
     */
    private $occurrences;

    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="questionnaire", cascade={"persist", "remove" })
     * @ORM\OrderBy({"ordre" = "ASC"})
     */
    private $questions;
    
    /**
     * @ORM\OneToMany(targetEntity="HopitalNumerique\UserBundle\Entity\RefusCandidature", mappedBy="questionnaire", cascade={"persist", "remove" })
     */
    protected $refusCandidature;
    
    /**
     * @ORM\OneToMany(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Outil", mappedBy="questionnairePrealable")
     */
    private $outils;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(name="hn_domaine_gestions_questionnaire",
     *      joinColumns={ @ORM\JoinColumn(name="qst_id", referencedColumnName="qst_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domaines;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe", mappedBy="questionnaire")
     */
    protected $communautePratiqueGroupes;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->questions    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lock = false;
        $this->occurrenceMultiple = false;
        $this->communautePratiqueGroupes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set occurrenceMultiple
     *
     * @param boolean $occurrenceMultiple
     * @return Questionnaire
     */
    public function setOccurrenceMultiple($occurrenceMultiple)
    {
        $this->occurrenceMultiple = $occurrenceMultiple;

        return $this;
    }

    /**
     * Get occurrenceMultiple
     *
     * @return boolean 
     */
    public function isOccurrenceMultiple()
    {
        return $this->occurrenceMultiple;
    }

    /**
     * Add occurrences
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Occurrence $occurrences
     * @return Questionnaire
     */
    public function addOccurrence(\HopitalNumerique\QuestionnaireBundle\Entity\Occurrence $occurrences)
    {
        $this->occurrences[] = $occurrences;

        return $this;
    }

    /**
     * Remove occurrences
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Occurrence $occurrences
     */
    public function removeOccurrence(\HopitalNumerique\QuestionnaireBundle\Entity\Occurrence $occurrences)
    {
        $this->occurrences->removeElement($occurrences);
    }

    /**
     * Get occurrences
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOccurrences()
    {
        return $this->occurrences;
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

    /**
     * Add refusCandidature
     *
     * @param \HopitalNumerique\UserBundle\Entity\RefusCandidature $refusCandidature
     * @return Questionnaire
     */
    public function addRefusCandidature(\HopitalNumerique\UserBundle\Entity\RefusCandidature $refusCandidature)
    {
        $this->refusCandidature[] = $refusCandidature;

        return $this;
    }

    /**
     * Remove refusCandidature
     *
     * @param \HopitalNumerique\UserBundle\Entity\RefusCandidature $refusCandidature
     */
    public function removeRefusCandidature(\HopitalNumerique\UserBundle\Entity\RefusCandidature $refusCandidature)
    {
        $this->refusCandidature->removeElement($refusCandidature);
    }

    /**
     * Get refusCandidature
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRefusCandidature()
    {
        return $this->refusCandidature;
    }

    /**
     * Set lien
     *
     * @param string $lien
     * @return Questionnaire
     */
    public function setLien($lien)
    {
        $this->lien = $lien;

        return $this;
    }

    /**
     * Get lien
     *
     * @return string 
     */
    public function getLien()
    {
        return $this->lien;
    }

    /**
     * Add outil
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outils
     * @return Questionnaire
     */
    public function addOutil(\HopitalNumerique\AutodiagBundle\Entity\Outil $outils)
    {
        $this->outils[] = $outils;

        return $this;
    }

    /**
     * Remove outil
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outils
     */
    public function removeOutil(\HopitalNumerique\AutodiagBundle\Entity\Outil $outils)
    {
        $this->outils->removeElement($outils);
    }

    /**
     * Get outils
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOutils()
    {
        return $this->outils;
    }

    /**
     * Add domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     * @return Questionnaire
     */
    public function addDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines[] = $domaines;

        return $this;
    }

    /**
     * Remove domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     */
    public function removeDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines->removeElement($domaines);
    }

    /**
     * Set domaines
     *
     * @param \Doctrine\Common\Collections\Collection $domaines
     * @return Domaine
     */
    public function setDomaines($domaines)
    {        
        $this->domaines = $domaines;
    
        return $this;
    }

    /**
     * Get domaines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDomaines()
    {
        return $this->domaines;
    }
    /**
     * Get les ids des domaines concerné par le questionnaire
     *
     * @return array[integer]
     */
    public function getDomainesId()
    {
        $domainesId = array();

        foreach ($this->domaines as $domaine) 
        {
            $domainesId[] = $domaine->getId();
        }

        return $domainesId;
    }

    /**
     * Add communautePratiqueGroupes
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes
     * @return Questionnaire
     */
    public function addCommunautePratiqueGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes)
    {
        $this->communautePratiqueGroupes[] = $communautePratiqueGroupes;

        return $this;
    }

    /**
     * Remove communautePratiqueGroupes
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes
     */
    public function removeCommunautePratiqueGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes)
    {
        $this->communautePratiqueGroupes->removeElement($communautePratiqueGroupes);
    }

    /**
     * Get communautePratiqueGroupes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCommunautePratiqueGroupes()
    {
        return $this->communautePratiqueGroupes;
    }
    
    
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->nom;
    }
}
