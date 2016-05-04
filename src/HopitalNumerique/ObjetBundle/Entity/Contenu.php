<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Contenu
 *
 * @ORM\Table(name="hn_objet_contenu")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\ContenuRepository")
 * @UniqueEntity(fields="alias", message="Cet alias existe déjà.")
 */
class Contenu
{
    /**
     * @var integer
     *
     * @ORM\Column(name="con_id", type="integer", options = {"comment" = "ID du contenu"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Le titre ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage = "Il doit y avoir au moins {{ limit }} caractères dans le titre.",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans le titre."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="con_titre", type="string", length=255, options = {"comment" = "Titre du contenu"})
     */
    protected $titre;

    /**
     * @var string
     * @Assert\Length(
     *      max = "255",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans l'alias."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[255]]")
     * @ORM\Column(name="con_alias", type="string", length=255, options = {"comment" = "Alias du contenu"})
     */
    protected $alias;

    /**
     * @var integer
     * 
     * @ORM\Column(name="con_order", type="integer", nullable=true, options = {"comment" = "Ordre du contenu"})
     */
    protected $order;

    /**
     * @var string
     * @Assert\NotBlank(message="Le contenu ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @ORM\Column(name="con_contenu", type="text", options = {"comment" = "Texte du contenu"})
     */
    protected $contenu;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="con_date_creation", type="datetime", options = {"comment" = "Date de création du contenu"})
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="con_date_modification", type="datetime", nullable=true, options = {"comment" = "Date de modification du contenu"})
     */
    private $dateModification;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\RefContenu", mappedBy="contenu", cascade={"persist", "remove" })
     */
    protected $references;

    /**
     * @ORM\ManyToOne(targetEntity="Contenu", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="con_id", onDelete="CASCADE")
     */
    protected $parent = null;
   
    /**
     * @ORM\ManyToOne(targetEntity="Objet", cascade={"persist"}, inversedBy="contenus")
     * @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id", onDelete="CASCADE")
     */
    protected $objet;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Consultation", mappedBy="contenu", cascade={"persist", "remove" })
     */
    protected $consultations;

    /**
     * @var integer
     *
     * @ORM\Column(name="con_nb_vue", type="integer", options = {"comment" = "Nombre de fois où le contenu à été vue"})     
     */
    protected $nbVue;

    /**
     * @var array
     *
     * @ORM\Column(name="con_glossaires", type="array", options = {"comment" = "Mots du glossaire liés au contenu"})
     */
    private $glossaires;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Commentaire", mappedBy="contenu", cascade={"persist", "remove" })
     */
    protected $listeCommentaires;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Note", mappedBy="contenu", cascade={"persist", "remove" })
     */
    protected $listeNotes;

    /**
     * @var array
     *
     * @ORM\Column(name="con_objets", type="array", nullable=true)
     */
    private $objets;


    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->titre        = 'Nouveau contenu';
        $this->alias        = 'nouveau-contenu';
        $this->contenu      = '';
        $this->parent       = null;
        $this->order        = 0;
        $this->nbVue        = 0;
        $this->references   = array();
        $this->glossaires   = array();
        $this->objets = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set titre
     *
     * @param string $titre
     * @return Contenu
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set alias
     *
     * @param string $alias
     * @return Contenu
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
     * Set contenu
     *
     * @param string $contenu
     * @return Contenu
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string 
     */
    public function getContenu()
    {
        return $this->contenu;
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
     */
    public function setReferences(\Doctrine\Common\Collections\ArrayCollection $references)
    {
        $this->references = $references;
    }
    
    /**
     * Get parent
     *
     * @return Contenu $parent
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * Set parent
     *
     * @param Contenu $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    
    /**
     * Get nbVue
     *
     * @return integer $nbVue
     */
    public function getNbVue()
    {
        return $this->nbVue;
    }
    
    /**
     * Set nbVue
     *
     * @param integer $nbVue
     */
    public function setNbVue($nbVue)
    {
        $this->nbVue = $nbVue;
        return $this;
    }
    
    /**
     * Get objet
     *
     * @return Objet $objet
     */
    public function getObjet()
    {
        return $this->objet;
    }
    
    /**
     * Set objet
     *
     * @param Objet $objet
     */
    public function setObjet(Objet $objet)
    {
        $this->objet = $objet;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Contenu
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
     * Set dateModification
     *
     * @param \DateTime $dateModification
     * @return Objet
     */
    public function setDateModification($dateModification)
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    /**
     * Get dateModification
     *
     * @return \DateTime 
     */
    public function getDateModification()
    {
        return $this->dateModification;
    }

    /**
     * Get consultations
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $consultations
     */
    public function getConsultations()
    {
        return $this->consultations;
    }

    /**
     * Set consultations
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $consultations
     * @return Objet
     */
    public function setConsultations(\Doctrine\Common\Collections\ArrayCollection $consultations)
    {        
        $this->consultations = $consultations;
    
        return $this;
    }

    /**
     * Get glossaires
     *
     * @return array $glossaires
     */
    public function getGlossaires()
    {
        return $this->glossaires;
    }
    
    /**
     * Set glossaires
     *
     * @param array $glossaires
     */
    public function setGlossaires(array $glossaires)
    {
        $this->glossaires = $glossaires;
        return $this;
    }

    /**
     * Remove glossaire
     *
     * @param string $glossaire
     */
    public function removeGlossaire($glossaire)
    {
        $this->glossaires->removeElement($glossaire);
    }
    
    /**
     * add glossaire
     *
     * @param string $glossaire
     */
    public function addGlossaire($glossaire)
    {
        $this->glossaires[] = $glossaire;
        return $this;
    }

    /**
     * Get listeCommentaires
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $listeCommentaires
     */
    public function getListeCommentaires()
    {
        return $this->listeCommentaires;
    }

    /**
     * Set listeCommentaires
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $listeCommentaires
     * @return Objet
     */
    public function setListeCommentaires(\Doctrine\Common\Collections\ArrayCollection $listeCommentaires)
    {        
        $this->listeCommentaires = $listeCommentaires;
    
        return $this;
    }

    /**
     * Get listeNotes
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $listeNotes
     */
    public function getListeNotes()
    {
        return $this->listeNotes;
    }

    /**
     * Set listeNotes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $listeNotes
     * @return Objet
     */
    public function setListeNotes(\Doctrine\Common\Collections\ArrayCollection $listeNotes)
    {        
        $this->listeNotes = $listeNotes;
    
        return $this;
    }

    /**
     * Add objet
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $objet
     *
     * @return Contenu
     */
    public function addObjet($objet)
    {
        $this->objets[] = $objet;

        return $this;
    }

    /**
     * Remove objet
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $objet
     */
    public function removeObjet($objet)
    {
        $this->objets->removeElement($objet);
    }

    /**
     * Remove all objets.
     */
    public function removeObjets()
    {
        $this->objets = new \Doctrine\Common\Collections\ArrayCollection();

        return $this;
    }

    /**
     * Get objets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObjets()
    {
        return $this->objets;
    }
}
