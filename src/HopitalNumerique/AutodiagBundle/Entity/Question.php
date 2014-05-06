<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     */
    private $texte;

    /**
     * @var string
     *
     * @ORM\Column(name="que_info_bulle", type="text", options = {"comment" = "Info Bulle de la question"}, nullable=true)
     */
    private $infoBulle;

    /**
     * @var integer
     *
     * @ORM\Column(name="que_ponderation", type="smallint", options = {"comment" = "Pondération de la question"})
     */
    private $ponderation;

    /**
     * @var integer
     *
     * @ORM\Column(name="que_ordre_resultat", type="smallint", options = {"comment" = "Ordre pour les résultats de la question"}, nullable=true)
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
     */
    private $noteMinimale;

    /**
     * @var integer
     *
     * @ORM\Column(name="que_seuil", type="smallint", options = {"comment" = "Valeur du seuil de déclenchement de la question"}, nullable=true)
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
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_type", referencedColumnName="ref_id")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="Chapitre", cascade={"persist"})
     * @ORM\JoinColumn(name="cha_id", referencedColumnName="cha_id")
     */
    protected $chapitre;

    /**
     * @ORM\ManyToOne(targetEntity="Categorie", cascade={"persist"})
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="cat_id")
     */
    protected $categorie;

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
     * @param integer $ponderation
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
     * @return integer 
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
}
