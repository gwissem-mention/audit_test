<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="cha_alias", type="string", length=255, options = {"comment" = "Alias du chapitre"})
     */
    private $alias;

    /**
     * @var integer
     *
     * @ORM\Column(name="cha_note_optimale", type="smallint", nullable=true, options = {"comment" = "Note optimale du chapitre"})
     */
    private $noteOptimale;

    /**
     * @var integer
     *
     * @ORM\Column(name="cha_note_minimale", type="smallint", nullable=true, options = {"comment" = "Note minimale du chapitre"})
     */
    private $noteMinimale;

    /**
     * @var string
     *
     * @ORM\Column(name="cha_synthese", type="text", nullable=true, options = {"comment" = "Phrase de synthèse du chapitre"})
     */
    private $synthese;

    /**
     * @var integer
     *
     * @ORM\Column(name="cha_order", type="smallint", options = {"comment" = "Ordre du chapitre"})
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="Outil", cascade={"persist"})
     * @ORM\JoinColumn(name="out_id", referencedColumnName="out_id")
     */
    protected $outil;

    /**
     * @ORM\ManyToOne(targetEntity="Chapitre", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="cha_id", onDelete="CASCADE", nullable=true)
     */
    protected $parent;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        
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
    
}
