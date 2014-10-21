<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categorie
 *
 * @ORM\Table(name="hn_outil_categorie")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\CategorieRepository")
 */
class Categorie
{
    /**
     * @var integer
     *
     * @ORM\Column(name="cat_id", type="integer", options = {"comment" = "ID de la categorie"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="cat_title", type="string", length=255, options = {"comment" = "Titre de la categorie"})
     */
    private $title;

    /**
     * @var integer
     *
     * @ORM\Column(name="cat_note", type="smallint", nullable=true, options = {"comment" = "Note optimale de la categorie"})
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity="Outil", cascade={"persist"}, inversedBy="categories")
     * @ORM\JoinColumn(name="out_id", referencedColumnName="out_id", onDelete="CASCADE")
     */
    protected $outil;

    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="categorie")
     */
    private $questions;

    /**
     * @var boolean
     *
     * @ORM\Column(name="cat_affichage_restitution", type="boolean", options = {"comment" = "Afficher lors de la restitution de l outil ?"})
     */
    private $affichageRestitution;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->questions            = new \Doctrine\Common\Collections\ArrayCollection();
        $this->affichageRestitution = true;
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
     * @return Categorie
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
     * Set note
     *
     * @param integer $note
     * @return Categorie
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return integer 
     */
    public function getNote()
    {
        return $this->note;
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
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Set affichageRestitution
     *
     * @param boolean $affichageRestitution
     * @return Categorie
     */
    public function setAffichageRestitution($affichageRestitution)
    {
        $this->affichageRestitution = $affichageRestitution;

        return $this;
    }

    /**
     * Get affichageRestitution
     *
     * @return boolean 
     */
    public function getAffichageRestitution()
    {
        return $this->affichageRestitution;
    }

    /**
     * Add questions
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Question $questions
     * @return Categorie
     */
    public function addQuestion(\HopitalNumerique\AutodiagBundle\Entity\Question $questions)
    {
        $this->questions[] = $questions;

        return $this;
    }

    /**
     * Remove questions
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Question $questions
     */
    public function removeQuestion(\HopitalNumerique\AutodiagBundle\Entity\Question $questions)
    {
        $this->questions->removeElement($questions);
    }
}
