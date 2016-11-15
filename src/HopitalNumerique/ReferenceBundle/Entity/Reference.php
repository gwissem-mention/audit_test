<?php

namespace HopitalNumerique\ReferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
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
     * @var integer ID de production
     */
    const CATEGORIE_OBJET_PRODUCTION_ID = 175;

    /**
     * @var integer ID de point dur
     */
    const CATEGORIE_OBJET_POINT_DUR_ID = 184;

    /**
     * @var integer ID de Retour d'expérience, témoignage
     */
    const CATEGORIE_OBJET_TEMOIGNAGE_ID = 176;

    /**
     * @var integer ID de l'ETAT_SUGGESTION Demande
     */
    const ETAT_SUGGESTION_DEMANDE_ID = 2005;

    /**
     * @var integer ID de l'ETAT_SUGGESTION Validé
     */
    const ETAT_SUGGESTION_VALIDE_ID = 2006;

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
     * @ORM\Column(name="ref_code", type="string", length=255, nullable=true, options = {"comment" = "Code de la référence"})
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
     * @ORM\Column(name="ref_order", type="float", options = {"comment" = "Ordre de la référence"})
     */
    protected $order;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Reference", inversedBy="enfants")
     * @ORM\JoinTable(
     *  name="hn_reference_has_parent",
     *  joinColumns={@ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="ref_parent_id", referencedColumnName="ref_id", onDelete="CASCADE")}
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
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference\Synonyme", cascade={"persist"}, inversedBy="references")
     * @ORM\JoinTable(name="hn_reference_has_synonyme",
     *      joinColumns={@ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="syn_id", referencedColumnName="syn_id", onDelete="CASCADE")}
     * )
     */
    private $synonymes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference\ChampLexicalNom", cascade={"persist"}, inversedBy="references")
     * @ORM\JoinTable(name="hn_reference_has_champ_lexical_nom",
     *      joinColumns={@ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="chl_id", referencedColumnName="chl_id", onDelete="CASCADE")}
     * )
     */
    private $champLexicalNoms;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ref_reference", type="boolean", nullable=false, options={"comment"="Est une référence ?", "default"=false})
     */
    private $reference;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ref_in_recherche", type="boolean", nullable=false, options={"comment"="Présente dans la recherche ?", "default"=false})
     */
    private $inRecherche;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_reference_libelle", type="string", length=255, nullable=true, options={"comment"="Libellé de la référence si différent du libellé du concept"})
     */
    private $referenceLibelle;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ref_in_glossaire", type="boolean", nullable=false, options={"comment"="Présente dans le glossaire ?", "default"=false})
     */
    private $inGlossaire;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_sigle", type="string", length=255, nullable=true)
     */
    private $sigle;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_glossaire_libelle", type="string", length=255, nullable=true, options={"comment"="Libellé du glossaire si différent du libellé du concept"})
     */
    private $glossaireLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_description_courte", type="text", nullable=true)
     */
    private $descriptionCourte;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ref_casse_sensible", type="boolean", nullable=false, options={"default"=false})
     */
    private $casseSensible;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_description_longue", type="text", nullable=true)
     */
    private $descriptionLongue;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine",
     *     cascade={"persist"}
     * )
     * @ORM\JoinTable(name="hn_reference_domaine_display",
     *      joinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domainesDisplay;


    /**
     * Constructeur.
     */
    public function __construct()
    {
        $this->lock  = false;
        $this->order = 1;
        $this->inRecherche = false;
        $this->domaines = new ArrayCollection();
        $this->allDomaines = false;
        $this->synonymes = new ArrayCollection();
        $this->champLexicalNoms = new ArrayCollection();
        $this->parents = new ArrayCollection();
        $this->enfants = new ArrayCollection();
        $this->domainesDisplay = new ArrayCollection();
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
     * Add parent
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $parent
     *
     * @return Reference
     */
    public function addParent(\HopitalNumerique\ReferenceBundle\Entity\Reference $parent)
    {
        $this->parents[] = $parent;

        return $this;
    }

    /**
     * Remove parent
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $parent
     */
    public function removeParent(\HopitalNumerique\ReferenceBundle\Entity\Reference $parent)
    {
        $this->parents->removeElement($parent);
    }

    /**
     * Get parents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * Get ID des parents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParentIds()
    {
        $parentIds = [];

        foreach ($this->parents as $parent) {
            $parentIds[] = $parent->getId();
        }

        return $parentIds;
    }

    /**
     * @deprecated Should not be used, fallback for old behavior
     * @return Reference|null
     */
    public function getParent()
    {
        return $this->getFirstParent();
    }

    /**
     * Get first parent
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference|null First parent
     */
    public function getFirstParent()
    {
        if (count($this->parents) > 0) {
            return $this->parents[0];
        }

        return null;
    }

    /**
     * Retourne si la référence à tel parent.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $referenceParent Parent
     * @return boolean Si a parent
     */
    public function hasParent(Reference $referenceParent)
    {
        foreach ($this->getParents() as $parent) {
            if ($referenceParent->getId() === $parent->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add enfant
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $enfant
     *
     * @return Reference
     */
    public function addEnfant(\HopitalNumerique\ReferenceBundle\Entity\Reference $enfant)
    {
        $this->enfants[] = $enfant;

        return $this;
    }

    /**
     * Remove enfant
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $enfant
     */
    public function removeEnfant(\HopitalNumerique\ReferenceBundle\Entity\Reference $enfant)
    {
        $this->enfants->removeElement($enfant);
    }

    /**
     * Get enfants
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEnfants()
    {
        return $this->enfants;
    }

    /**
     * Add domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine
     * @return Reference
     */
    public function addDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        $this->domaines[] = $domaine;

        return $this;
    }

    /**
     * Add domaines
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines Domaines
     * @return Reference
     */
    public function addDomaines($domaines)
    {
        foreach ($domaines as $domaine) {
            $this->addDomaine($domaine);
        }

        return $this;
    }

    /**
     * Remove domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine
     */
    public function removeDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        $this->removeDomainesDisplay($domaine);
        $this->domaines->removeElement($domaine);

    }

    /**
     * Supprime tous les domaines.
     */
    public function removeDomaines()
    {
        $this->domaines = new ArrayCollection();
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
     * Retourne les noms des domaines.
     *
     * @return array<string> Noms
     */
    public function getDomaineNoms()
    {
        $domaineLibelles = [];

        foreach ($this->domaines as $domaine) {
            $domaineLibelles[] = $domaine->getNom();
        }

        return $domaineLibelles;
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
     * Retourne si la référence est liée au domaine.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     */
    public function hasDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        if (true === $this->allDomaines) {
            return true;
        }

        foreach ($this->domaines as $domaineExistant) {
            if ($domaineExistant->equals($domaine)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne si la référence possède l'un des domaines.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines Domaines
     */
    public function hasAtLeastOneDomaine($domaines)
    {
        foreach ($domaines as $domaine) {
            if ($this->hasDomaine($domaine)) {
                return true;
            }
        }

        return false;
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
    public function isAllDomaines()
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
        if (!$this->hasSynonyme($synonyme)) {
            $this->synonymes[] = $synonyme;
        }

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
     * Retourne si la référence possède déjà tel synonyme.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference\Synonyme $synonyme Synonyme
     * @return boolean si existant
     */
    public function hasSynonyme(\HopitalNumerique\ReferenceBundle\Entity\Reference\Synonyme $synonyme)
    {
        foreach ($this->synonymes as $synonymeExistant) {
            if ($synonymeExistant->equals($synonyme)) {
                return true;
            }
        }

        return false;
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
        if (!$this->hasChampLexicalNom($champLexicalNom)) {
            $this->champLexicalNoms[] = $champLexicalNom;
        }

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

    /**
     * Retourne si la référence possède déjà tel champLexicalNom.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference\ChampLexicalNom $champLexicalNom ChampLexicalNom
     * @return boolean si existant
     */
    public function hasChampLexicalNom(\HopitalNumerique\ReferenceBundle\Entity\Reference\ChampLexicalNom $champLexicalNom)
    {
        foreach ($this->champLexicalNoms as $champLexicalNomExistant) {
            if ($champLexicalNomExistant->equals($champLexicalNom)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set reference
     *
     * @param boolean $reference
     *
     * @return Reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return boolean
     */
    public function isReference()
    {
        return $this->reference;
    }

    /**
     * Set inRecherche
     *
     * @param boolean $inRecherche
     *
     * @return Reference
     */
    public function setInRecherche($inRecherche)
    {
        $this->inRecherche = $inRecherche;

        return $this;
    }

    /**
     * Get inRecherche
     *
     * @return boolean
     */
    public function isInRecherche()
    {
        return $this->inRecherche;
    }

    /**
     * Set referenceLibelle
     *
     * @param string $referenceLibelle
     *
     * @return Reference
     */
    public function setReferenceLibelle($referenceLibelle)
    {
        $this->referenceLibelle = $referenceLibelle;

        return $this;
    }

    /**
     * Get referenceLibelle
     *
     * @return string
     */
    public function getReferenceLibelle()
    {
        return $this->referenceLibelle;
    }

    /**
     * Set inGlossaire
     *
     * @param boolean $inGlossaire
     *
     * @return Reference
     */
    public function setInGlossaire($inGlossaire)
    {
        $this->inGlossaire = $inGlossaire;

        return $this;
    }

    /**
     * Get inGlossaire
     *
     * @return boolean
     */
    public function isInGlossaire()
    {
        return $this->inGlossaire;
    }

    /**
     * Set sigle
     *
     * @param string $sigle
     *
     * @return Reference
     */
    public function setSigle($sigle)
    {
        $this->sigle = $sigle;

        return $this;
    }

    /**
     * Get sigle
     *
     * @return string
     */
    public function getSigle()
    {
        return $this->sigle;
    }

    /**
     * Set glossaireLibelle
     *
     * @param string $glossaireLibelle
     *
     * @return Reference
     */
    public function setGlossaireLibelle($glossaireLibelle)
    {
        $this->glossaireLibelle = $glossaireLibelle;

        return $this;
    }

    /**
     * Get glossaireLibelle
     *
     * @return string
     */
    public function getGlossaireLibelle()
    {
        return $this->glossaireLibelle;
    }

    /**
     * Set descriptionCourte
     *
     * @param string $descriptionCourte
     *
     * @return Reference
     */
    public function setDescriptionCourte($descriptionCourte)
    {
        $this->descriptionCourte = $descriptionCourte;

        return $this;
    }

    /**
     * Get descriptionCourte
     *
     * @return string
     */
    public function getDescriptionCourte()
    {
        return $this->descriptionCourte;
    }

    /**
     * Set descriptionLongue
     *
     * @param string $descriptionLongue
     *
     * @return Reference
     */
    public function setDescriptionLongue($descriptionLongue)
    {
        $this->descriptionLongue = $descriptionLongue;

        return $this;
    }

    /**
     * Get descriptionLongue
     *
     * @return string
     */
    public function getDescriptionLongue()
    {
        return $this->descriptionLongue;
    }

    /**
     * Set casseSensible
     *
     * @param boolean $casseSensible
     *
     * @return Reference
     */
    public function setCasseSensible($casseSensible)
    {
        $this->casseSensible = $casseSensible;

        return $this;
    }

    /**
     * Get casseSensible
     *
     * @return boolean
     */
    public function isCasseSensible()
    {
        return $this->casseSensible;
    }


    public function __toString()
    {
        return $this->code . ' - ' . $this->libelle;
    }

    /**
     * Retourne l'égalité entre deux références.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference Autre référence
     * @return boolean Si égalité
     */
    public function equals(Reference $reference)
    {
        return ($this->id === $reference->getId());
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

    /**
     * Retourne le libellé à afficher pour une référence.
     *
     * @return string Libellé
     */
    public function getLibelleForReference()
    {
        return ('' != $this->referenceLibelle ? $this->referenceLibelle : $this->libelle);
    }

    /**
     * Retourne le sigle pour le glossaire.
     *
     * @return string Sigle
     */
    public function getSigleForGlossaire()
    {
        return (null !== $this->sigle ? $this->sigle : (null !== $this->glossaireLibelle ? $this->glossaireLibelle : $this->libelle));
    }

    /**
     * Retourne le sigle encodé en HTML pour le glossaire.
     *
     * @return string HTML
     */
    public function getSigleHtmlForGlossaire()
    {
        return htmlentities($this->getSigleForGlossaire());
    }

    public function getDomainesDisplay()
    {
        return $this->domainesDisplay;
    }

    public function addDomainesDisplay(Domaine $domaine)
    {
        if (!$this->domainesDisplay->contains($domaine)) {
            $this->domainesDisplay->add($domaine);
        }
    }

    public function removeDomainesDisplay(Domaine $domaine)
    {
        $this->domainesDisplay->remove($domaine);
    }

    public function getDomainesDisplayId()
    {
        return $this->domainesDisplay->map(function (Domaine $domaine) {
            return $domaine->getId();
        });
    }

}
