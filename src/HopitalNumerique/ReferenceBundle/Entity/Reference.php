<?php

namespace HopitalNumerique\ReferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Reference
 *
 * @ORM\Table(name="hn_reference")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ReferenceBundle\Repository\ReferenceRepository")
 * @UniqueEntity(fields={"libelle","code"}, message="Ce libellé pour ce code existe déjà.")
 */
class Reference
{
    /**
     * @var integer ID de Monsieur
     */
    const CIVILITE_MONSIEUR_ID = 8;

    /**
     * @var integer ID de Madame
     */
    const CIVILITE_MADAME_ID = 9;

    /**
     * @var integer ID de la catégorie d'article de la communauté de partiques
     */
    const ARTICLE_CATEGORIE_COMMUNAUTE_DE_PRATIQUES_ID = 800;


    const STATUT_ACTIF_ID = 3;
    const STATUT_INACTIF_ID = 4;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="ref_id", type="integer", options = {"comment" = "ID de la référence"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Le libellé ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le libellé.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le libellé."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="ref_libelle", type="string", length=255, options = {"comment" = "Libellé de la référence"})
     */
    protected $libelle;

    /**
     * @var string
     * @Assert\NotBlank(message="Le code ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le code.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le code."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="ref_code", type="string", length=255, options = {"comment" = "Code de la référence"})
     */
    protected $code;

    /**
     * @Assert\NotBlank(message="Merci de choisir un état")
     * @Nodevo\Javascript(class="validate[required]")
     * @ORM\ManyToOne(targetEntity="Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat", referencedColumnName="ref_id")
     */
    protected $etat;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ref_dictionnaire", type="boolean", options = {"comment" = "Référence présente dans dictionnaire de référencement ?"})
     */
    protected $dictionnaire;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ref_recherche", type="boolean", options = {"comment" = "Référence présente dans le moteur de recherche ?"})
     */
    protected $recherche;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ref_lock", type="boolean", options = {"comment" = "Référence verrouillée ?"})
     */
    protected $lock;

    /**
     * @ORM\ManyToOne(targetEntity="Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="ref_id")
     */
    protected $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="Reference", mappedBy="parent")
     */
    protected $childs;

    /**
     * @var float
     * @Assert\NotBlank(message="L'ordre ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required, custom[number]]")
     * @ORM\Column(name="ref_order", type="float", options = {"comment" = "Ordre de la référence"})
     */
    protected $order;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(name="hn_domaine_gestions_reference",
     *      joinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domaines;

    public function __construct()
    {
        $this->lock  = false;
        $this->order = 1;
    }

    public function __toString()
    {
        return $this->code . ' - ' . $this->libelle;
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
     * Set libelle
     *
     * @param string $libelle
     * @return Reference
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Reference
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set etat
     *
     * @param Reference $etat
     * @return Reference
     */
    public function setEtat(Reference $etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return Reference 
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Set dictionnaire
     *
     * @param boolean $dictionnaire
     * @return Reference
     */
    public function setDictionnaire($dictionnaire)
    {
        $this->dictionnaire = $dictionnaire;

        return $this;
    }

    /**
     * Get dictionnaire
     *
     * @return boolean 
     */
    public function getDictionnaire()
    {
        return $this->dictionnaire;
    }

    /**
     * Set recherche
     *
     * @param boolean $recherche
     * @return Reference
     */
    public function setRecherche($recherche)
    {
        $this->recherche = $recherche;

        return $this;
    }

    /**
     * Get recherche
     *
     * @return boolean 
     */
    public function getRecherche()
    {
        return $this->recherche;
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
     * Get parent
     *
     * @return Reference $parent
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * Set parent
     *
     * @param Reference $parent
     */
    public function setParent($parent)
    {
        if( $parent instanceof Reference)
        	$this->parent = $parent;
        else
            $this->parent = null;        
    }

    /**
     * Get order
     *
     * @return float $order
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * Set order
     *
     * @param float $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }
    
    /**
     * Retourne le nom du parent
     *
     * @return string|null
     */
    public function getParentName()
    {
        if( null === $this->getParent() )
            return null;

        return $this->getParent()->getLibelle();
    }

    /**
     * Retourne le nom de l'arbo
     *
     * @return string
     */
    public function getArboName()
    {
        //retourne le niveau de profondeur de l'élément courent
        $level = $this->getLevel( 0, $this );
        $sep   = '';

        //selon le level de profondeur, le séparateur change
        if( $level > 1 ) {
            for( $i = 2; $i <= $level; $i++ )
                $sep .= '|--';
        }

        return $sep . $this->code . ' - ' . $this->libelle;
    }

    /**
     * Retourne les liste des enfants de la Collection
     *
     * @param  ArrayCollection $collection La collection
     *
     * @return array
     */
    public function getChildsFromCollection( ArrayCollection $collection )
    {
        $criteria = Criteria::create()
                                    ->where(Criteria::expr()->eq("parent", $this))
                                    ->orderBy( array("order" => Criteria::ASC) );
        return $collection->matching( $criteria );
    }

    /**
     * Retourne le niveau de profondeur de l'élément
     *
     * @param integer   $currentLevel Niveau de profondeur actuel
     * @param Reference $ref          Profondeur concernée
     *
     * @return integer
     */
    private function getLevel( $currentLevel, Reference $ref )
    {
        $currentLevel++;
        if( $ref->getParent() )
            $currentLevel = $this->getLevel( $currentLevel, $ref->getParent() );

        return $currentLevel;
    }

    /**
     * Retourne l'ID pour le statut actif.
     * 
     * @return integer ID pour le statut actif
     */
    public static function getStatutActifId()
    {
        return self::STATUT_ACTIF_ID;
    }

    /**
     * Add domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     * @return Reference
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
     * Get domaines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /**
     * Get les ids des domaines concerné par l'user
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
     * set domaines
     *
     * @return Reference
     */
    public function setDomaines($domaines = null)
    {
        $this->domaines = $domaines;

        return $this;
    }

    /**
     * Add childs
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $childs
     * @return Reference
     */
    public function addChild(\HopitalNumerique\ReferenceBundle\Entity\Reference $childs)
    {
        $this->childs[] = $childs;

        return $this;
    }

    /**
     * Remove childs
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $childs
     */
    public function removeChild(\HopitalNumerique\ReferenceBundle\Entity\Reference $childs)
    {
        $this->childs->removeElement($childs);
    }

    /**
     * Get childs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChilds()
    {
        return $this->childs;
    }
}
