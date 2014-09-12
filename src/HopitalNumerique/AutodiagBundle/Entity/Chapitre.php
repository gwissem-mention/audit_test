<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Chapitre
 *
 * @ORM\Table(name="hn_outil_chapitre")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\ChapitreRepository")
 */
class Chapitre
{
    /**
     * @var integer
     *
     * @ORM\Column(name="cha_id", type="integer", options = {"comment" = "ID du chapitre"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="cha_title", type="string", length=255, options = {"comment" = "Titre du chapitre"})
     * @Assert\NotBlank(message="Le titre ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage = "Il doit y avoir au moins {{ limit }} caractères dans le titre.",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans le titre."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="cha_alias", type="string", length=255, options = {"comment" = "Alias du chapitre"})
     * @Assert\Length(
     *      max = "255",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans le titre."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[255]]")
     */
    private $alias;

    /**
     * @var string
     *
     * @ORM\Column(name="cha_code", type="string", length=8, options = {"comment" = "Code du chapitre"}, nullable=true)
     * @Assert\Length(
     *      max = "8",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans le code."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[8]]")
     */
    private $code;

    /**
     * @var integer
     *
     * @ORM\Column(name="cha_note_optimale", type="smallint", nullable=true, options = {"comment" = "Note optimale du chapitre"})
     * @Nodevo\Javascript(class="validate[custom[integer], min[0], max[999]]")
     */
    private $noteOptimale;

    /**
     * @var integer
     *
     * @ORM\Column(name="cha_note_minimale", type="smallint", nullable=true, options = {"comment" = "Note minimale du chapitre"})
     * @Nodevo\Javascript(class="validate[custom[integer], min[0], max[999]]")
     */
    private $noteMinimale;

    /**
     * @var string
     *
     * @ORM\Column(name="cha_synthese", type="text", nullable=true, options = {"comment" = "Phrase de synthèse du chapitre"})
     */
    private $synthese;

    /**
     * @var string
     *
     * @ORM\Column(name="cha_intro", type="text", nullable=true, options = {"comment" = "Introduction du chapitre"})
     */
    private $intro;

    /**
     * @var string
     *
     * @ORM\Column(name="cha_desc", type="text", nullable=true, options = {"comment" = "Description du chapitre"})
     */
    private $desc;

    /**
     * @var integer
     *
     * @ORM\Column(name="cha_order", type="smallint", options = {"comment" = "Ordre du chapitre"})
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="Outil", cascade={"persist"}, inversedBy="chapitres")
     * @ORM\JoinColumn(name="out_id", referencedColumnName="out_id", onDelete="CASCADE")
     */
    protected $outil;

    /**
     * @ORM\ManyToOne(targetEntity="Chapitre", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="cha_id", onDelete="CASCADE", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="chapitre")
     * @ORM\OrderBy({"ordreResultat" = "DESC", "order" = "ASC"})
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\AutodiagBundle\Entity\RefChapitre", mappedBy="chapitre", cascade={"persist"})
     */
    protected $references;
    
    /**
     * @var \Doctrine\Common\Collections\Collection Les outils de diagnostique lors d'une restitution par process
     *
     * @ORM\OneToMany(
     *   targetEntity = "ProcessChapitre",
     *   mappedBy = "chapitre",
     *   cascade = { "persist" }
     * )
     */
    private $processChapitres;
    
    /**
     * @var float Moyenne des résultats
     */
    private $resultatsMoyenne;
    /**
     * @var int Nombre de questions qui ont été répondues
     */
    private $nombreQuestionsRepondues;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Chapitre
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
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
     * Set alias
     *
     * @param string $alias
     * @return Chapitre
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string 
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
    
    /**
     * Get noteOptimale
     *
     * @return integer $noteOptimale
     */
    public function getNoteOptimale()
    {
        return $this->noteOptimale;
    }
    
    /**
     * Set noteOptimale
     *
     * @param integer $noteOptimale
     */
    public function setNoteOptimale($noteOptimale)
    {
        $this->noteOptimale = $noteOptimale;
        return $this;
    }
    
    /**
     * Get noteMinimale
     *
     * @return integer $noteMinimale
     */
    public function getNoteMinimale()
    {
        return $this->noteMinimale;
    }
    
    /**
     * Set noteMinimale
     *
     * @param integer $noteMinimale
     */
    public function setNoteMinimale($noteMinimale)
    {
        $this->noteMinimale = $noteMinimale;
        return $this;
    }
    
    /**
     * Set synthese
     *
     * @param string $synthese
     * @return Chapitre
     */
    public function setSynthese($synthese)
    {
        $this->synthese = $synthese;

        return $this;
    }

    /**
     * Get synthese
     *
     * @return string 
     */
    public function getSynthese()
    {
        return $this->synthese;
    }

    /**
     * Get intro
     *
     * @return string $intro
     */
    public function getIntro()
    {
        return $this->intro;
    }
    
    /**
     * Set intro
     *
     * @param string $intro
     */
    public function setIntro($intro)
    {
        $this->intro = $intro;
        return $this;
    }
    
    /**
     * Get desc
     *
     * @return string $desc
     */
    public function getDesc()
    {
        return $this->desc;
    }
    
    /**
     * Set desc
     *
     * @param string $desc
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;
        return $this;
    }

    /**
     * Get order
     *
     * @return integer $order
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * Set order
     *
     * @param integer $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }
    
    /**
     * Get outil
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Outil $outil
     */
    public function getOutil()
    {
        return $this->outil;
    }
    
    /**
     * Set outil
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outil
     */
    public function setOutil(\HopitalNumerique\AutodiagBundle\Entity\Outil $outil)
    {
        $this->outil = $outil;
        return $this;
    }

    /**
     * Get parent
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Chapitre $parent
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * Set parent
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Chapitre $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
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
     * Get references
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $references
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Set references
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $references
     * @return Objet
     */
    public function setReferences(\Doctrine\Common\Collections\ArrayCollection $references)
    {        
        $this->references = $references;
    
        return $this;
    }

    /**
     * Get resultatsMoyenne
     *
     * @return string $resultatsMoyenne
     */
    public function getResultatsMoyenne()
    {
        return $this->resultatsMoyenne;
    }
    
    /**
     * Set resultatsMoyenne
     *
     * @param string $resultatsMoyenne
     */
    public function setResultatsMoyenne($resultatsMoyenne)
    {
        $this->resultatsMoyenne = $resultatsMoyenne;
        return $this;
    }
    
    /**
     * Get nombreQuestionsRepondues
     *
     * @return string $nombreQuestionsRepondues
     */
    public function getNombreQuestionsRepondues()
    {
        return $this->nombreQuestionsRepondues;
    }
    
    /**
     * Set nombreQuestionsRepondues
     *
     * @param string $nombreQuestionsRepondues
     */
    public function setNombreQuestionsRepondues($nombreQuestionsRepondues)
    {
        $this->nombreQuestionsRepondues = $nombreQuestionsRepondues;
        return $this;
    }
}
