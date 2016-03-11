<?php

namespace HopitalNumerique\ReferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Nodevo\ToolsBundle\Tools\Systeme;
use Nodevo\ToolsBundle\Traits\ImageTrait;
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
    use ImageTrait;


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

    /**
     * @var integer
     */
    const STATUT_ACTIF_ID = 3;

    /**
     * @var integer
     */
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
     * @var string
     *
     * @ORM\Column(name="ref_image", type="text", nullable=true, length=255)
     */
    protected $image;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $imageFile;

    /**
     * @var float
     * @Assert\NotBlank(message="L'ordre ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required, custom[number]]")
     * @ORM\Column(name="ref_order", type="float", options = {"comment" = "Ordre de la référence"})
     */
    protected $order;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Reference", inversedBy="enfants")
     * @ORM\JoinTable(
     *  name="hn_reference_has_parent",
     *  joinColumns={@ORM\JoinColumn(name="ref_parent_id", referencedColumnName="ref_id", onDelete="CASCADE")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")}
     * )
     */
    protected $parents;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Reference", mappedBy="parents")
     */
    protected $enfants;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(name="hn_domaine_gestions_reference",
     *      joinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domaines;

    /**
     * @var bool
     *
     * @ORM\Column(name="ref_all_domaines", type="boolean", nullable=false, options={"default"=false, "comment"="Tous les domaines sont liés au concept"})
     */
    private $allDomaines;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference\Synonyme", cascade={"persist"})
     * @ORM\JoinTable(name="hn_reference_has_synonyme",
     *      joinColumns={@ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="syn_id", referencedColumnName="syn_id", onDelete="CASCADE")}
     * )
     */
    private $synonymes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference\ChampLexicalNom", cascade={"persist"})
     * @ORM\JoinTable(name="hn_reference_has_champ_lexical_nom",
     *      joinColumns={@ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="chl_id", referencedColumnName="chl_id", onDelete="CASCADE")}
     * )
     */
    private $champLexicalNoms;


    /**
     * Constructeur.
     */
    public function __construct()
    {
        $this->lock  = false;
        $this->order = 1;
        $this->domaines = new ArrayCollection();
        $this->allDomaines = false;
        $this->synonymes = new ArrayCollection();
        $this->champLexicalNoms = new ArrayCollection();
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
     * Retourne le nom de l'arbo
     *
     * @return string
     */
    /*public function getArboName()
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
    }*/

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
     * Set allDomaines
     *
     * @param boolean $allDomaines
     *
     * @return Reference
     */
    public function setAllDomaines($allDomaines)
    {
        $this->allDomaines = $allDomaines;

        return $this;
    }

    /**
     * Get allDomaines
     *
     * @return boolean
     */
    public function getAllDomaines()
    {
        return $this->allDomaines;
    }

    /**
     * Add synonyme
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference\Synonyme $synonyme
     *
     * @return Reference
     */
    public function addSynonyme(\HopitalNumerique\ReferenceBundle\Entity\Reference\Synonyme $synonyme)
    {
        $this->synonymes[] = $synonyme;

        return $this;
    }

    /**
     * Remove synonyme
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference\Synonyme $synonyme
     */
    public function removeSynonyme(\HopitalNumerique\ReferenceBundle\Entity\Reference\Synonyme $synonyme)
    {
        $this->synonymes->removeElement($synonyme);
    }

    /**
     * Get synonymes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSynonymes()
    {
        return $this->synonymes;
    }

    /**
     * Add champLexicalNom
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference\ChampLexicalNom $champLexicalNom
     *
     * @return Reference
     */
    public function addChampLexicalNom(\HopitalNumerique\ReferenceBundle\Entity\Reference\ChampLexicalNom $champLexicalNom)
    {
        $this->champLexicalNoms[] = $champLexicalNom;

        return $this;
    }

    /**
     * Remove champLexicalNom
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference\ChampLexicalNom $champLexicalNom
     */
    public function removeChampLexicalNom(\HopitalNumerique\ReferenceBundle\Entity\Reference\ChampLexicalNom $champLexicalNom)
    {
        $this->champLexicalNoms->removeElement($champLexicalNom);
    }

    /**
     * Get champLexicalNoms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChampLexicalNoms()
    {
        return $this->champLexicalNoms;
    }


    public function __toString()
    {
        return $this->code . ' - ' . $this->libelle;
    }


    /**
     * {@inheritdoc}
     */
    public function getImageUploadDir()
    {
        return 'media'.DIRECTORY_SEPARATOR.'referentiel';
    }

    /**
     * {@inheritdoc}
     */
    public function imageFileIsValid()
    {
        return (null !== $this->imageFile && $this->imageFile->getClientSize() <= Systeme::getFileUploadMaxSize());
    }

    /**
     * Retourne l'URL de l'image.
     *
     * @return string|null URL
     */
    public function getImageUrl()
    {
        if (null !== $this->image) {
            return '/'.str_replace(DIRECTORY_SEPARATOR, '/', $this->getImageUploadDir()).'/'.$this->image;
        }

        return null;
    }
}
