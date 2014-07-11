<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Question
 *
 * @ORM\Table(name="hn_outil_question")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @var integer
     *
     * @ORM\Column(name="que_id", type="integer", options = {"comment" = "ID de la question"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="que_texte", type="string", length=512, options = {"comment" = "Texte de la question"})
     * @Assert\NotBlank(message="Le texte ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "512",
     *      minMessage = "Il doit y avoir au moins {{ limit }} caractères dans le texte.",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans le texte."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[512]]")
     */
    private $texte;

    /**
     * @var string
     *
     * @ORM\Column(name="que_code", type="string", length=8, options = {"comment" = "Code de la question"}, nullable=true)
     * @Assert\Length(
     *      max = "8",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans le code."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[8]]")
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="que_info_bulle", type="text", options = {"comment" = "Info Bulle de la question"}, nullable=true)
     */
    private $infoBulle;

    /**
     * @var float
     *
     * @ORM\Column(name="que_ponderation", type="float", options = {"comment" = "Ponderation de la question"})
     * @Nodevo\Javascript(class="validate[required,custom[number], min[0], max[100]]")
     */
    private $ponderation;

    /**
     * @var integer
     *
     * @ORM\Column(name="que_ordre_resultat", type="smallint", options = {"comment" = "Ordre pour les résultats de la question"}, nullable=true)
     * @Nodevo\Javascript(class="validate[custom[integer], min[0], max[999]]")
     */
    private $ordreResultat;

    /**
     * @var string
     *
     * @ORM\Column(name="que_options", type="text", options = {"comment" = "Items de choix de réponse de la question"}, nullable=true)
     */
    private $options;

    /**
     * @var integer
     *
     * @ORM\Column(name="que_note_minimale", type="smallint", options = {"comment" = "Note minimale de déclenchement de la question"}, nullable=true)
     * @Nodevo\Javascript(class="validate[custom[integer], min[0], max[999]]")
     */
    private $noteMinimale;

    /**
     * @var integer
     *
     * @ORM\Column(name="que_seuil", type="smallint", options = {"comment" = "Valeur du seuil de déclenchement de la question"}, nullable=true)
     * @Nodevo\Javascript(class="validate[custom[integer], min[0], max[999]]")
     */
    private $seuil;

    /**
     * @var string
     *
     * @ORM\Column(name="que_synthese", type="text", options = {"comment" = "Synthèse de la question"}, nullable=true)
     */
    private $synthese;

    /**
     * @var integer
     *
     * @ORM\Column(name="que_order", type="integer", options = {"comment" = "Ordre de la question"})
     */
    private $order;

    /**
     * @var boolean
     *
     * @ORM\Column(name="que_colored", type="boolean", options = {"comment" = "Colorer la question ?"})
     */
    private $colored;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_type", referencedColumnName="ref_id")
     * @Nodevo\Javascript(class="validate[required]")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="Chapitre", cascade={"persist"}, inversedBy="questions" )
     * @ORM\JoinColumn(name="cha_id", referencedColumnName="cha_id", onDelete="CASCADE")
     */
    protected $chapitre;

    /**
     * @ORM\ManyToOne(targetEntity="Categorie", cascade={"persist"}, inversedBy="questions")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="cat_id", onDelete="CASCADE")
     * @Nodevo\Javascript(class="validate[required]")
     */
    protected $categorie;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\AutodiagBundle\Entity\RefQuestion", mappedBy="question", cascade={"persist"})
     */
    protected $references;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->ponderation = 1;
        $this->colored     = 0;
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
     * Set texte
     *
     * @param string $texte
     * @return Question
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;

        return $this;
    }

    /**
     * Get texte
     *
     * @return string 
     */
    public function getTexte()
    {
        return $this->texte;
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
     * Set infoBulle
     *
     * @param string $infoBulle
     * @return Question
     */
    public function setInfoBulle($infoBulle)
    {
        $this->infoBulle = $infoBulle;

        return $this;
    }

    /**
     * Get infoBulle
     *
     * @return string 
     */
    public function getInfoBulle()
    {
        return $this->infoBulle;
    }

    /**
     * Set ponderation
     *
     * @param float $ponderation
     * @return Question
     */
    public function setPonderation($ponderation)
    {
        $this->ponderation = $ponderation;

        return $this;
    }

    /**
     * Get ponderation
     *
     * @return float 
     */
    public function getPonderation()
    {
        return $this->ponderation;
    }

    /**
     * Set ordreResultat
     *
     * @param integer $ordreResultat
     * @return Question
     */
    public function setOrdreResultat($ordreResultat)
    {
        $this->ordreResultat = $ordreResultat;

        return $this;
    }

    /**
     * Get ordreResultat
     *
     * @return integer 
     */
    public function getOrdreResultat()
    {
        return $this->ordreResultat;
    }

    /**
     * Set options
     *
     * @param string $options
     * @return Question
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options
     *
     * @return string 
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set noteMinimale
     *
     * @param integer $noteMinimale
     * @return Question
     */
    public function setNoteMinimale($noteMinimale)
    {
        $this->noteMinimale = $noteMinimale;

        return $this;
    }

    /**
     * Get noteMinimale
     *
     * @return integer 
     */
    public function getNoteMinimale()
    {
        return $this->noteMinimale;
    }

    /**
     * Set seuil
     *
     * @param integer $seuil
     * @return Question
     */
    public function setSeuil($seuil)
    {
        $this->seuil = $seuil;

        return $this;
    }

    /**
     * Get seuil
     *
     * @return integer 
     */
    public function getSeuil()
    {
        return $this->seuil;
    }

    /**
     * Set synthese
     *
     * @param string $synthese
     * @return Question
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
     * Set order
     *
     * @param integer $order
     * @return Question
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer 
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get colored
     *
     * @return boolean $colored
     */
    public function getColored()
    {
        return $this->colored;
    }
    
    /**
     * Set colored
     *
     * @param boolean $colored
     */
    public function setColored($colored)
    {
        $this->colored = $colored;
        return $this;
    }
    
    /**
     * Get type
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $type
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set type
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $type
     */
    public function setType(\HopitalNumerique\ReferenceBundle\Entity\Reference $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get chapitre
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Chapitre $chapitre
     */
    public function getChapitre()
    {
        return $this->chapitre;
    }
    
    /**
     * Set chapitre
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Chapitre $chapitre
     */
    public function setChapitre(\HopitalNumerique\AutodiagBundle\Entity\Chapitre $chapitre)
    {
        $this->chapitre = $chapitre;
        return $this;
    }
    
    /**
     * Get categorie
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Categorie $categorie
     */
    public function getCategorie()
    {
        return $this->categorie;
    }
    
    /**
     * Set categorie
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Categorie $categorie
     */
    public function setCategorie(\HopitalNumerique\AutodiagBundle\Entity\Categorie $categorie)
    {
        $this->categorie = $categorie;
        return $this;
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
}
