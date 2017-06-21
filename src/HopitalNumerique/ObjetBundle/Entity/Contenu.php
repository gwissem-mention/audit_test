<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Contenu.
 *
 * @ORM\Table(name="hn_objet_contenu")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\ContenuRepository")
 * @UniqueEntity(fields="alias", message="Cet alias existe déjà.")
 */
class Contenu
{
    /**
     * @var int
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
     * @ORM\ManyToOne(targetEntity="Contenu", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="con_id", onDelete="CASCADE")
     */
    protected $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="Contenu", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Objet", cascade={"persist"}, inversedBy="contenus", fetch="EAGER")
     * @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id", onDelete="CASCADE")
     */
    protected $objet;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_contenu_type",
     *      joinColumns={ @ORM\JoinColumn(name="con_id", referencedColumnName="con_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="type_id", referencedColumnName="ref_id", onDelete="CASCADE")}
     * )
     */
    private $types;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Consultation", mappedBy="contenu", cascade={"persist", "remove" })
     */
    protected $consultations;

    /**
     * @var int
     *
     * @ORM\Column(name="con_nb_vue", type="integer", options = {"comment" = "Nombre de fois où le contenu à été vue"})
     */
    protected $nbVue;

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
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine")
     * @ORM\JoinTable(name="hn_domaine_gestions_contenu",
     *      joinColumns={ @ORM\JoinColumn(name="con_id", referencedColumnName="con_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    private $domaines;

    /**
     * Initialisation de l'entitée (valeurs par défaut).
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->titre = 'Nouveau contenu';
        $this->alias = 'nouveau-contenu';
        $this->contenu = '';
        $this->parent = null;
        $this->order = 0;
        $this->nbVue = 0;
        $this->objets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->domaines = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set titre.
     *
     * @param string $titre
     *
     * @return Contenu
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre.
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Get title tree concatenated.
     * Used for search indexation
     *
     * @return string
     */
    public function getTitleTree()
    {
        $title = $this->getTitre();

        if ($this->getParent()) {
            $title = $this->getParent()->getTitre() . ' ' . $title;
        }

        return $this->getObjet()->getTitre() . ' ' . $title;
    }

    /**
     * Set alias.
     *
     * @param string $alias
     *
     * @return Contenu
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set contenu.
     *
     * @param string $contenu
     *
     * @return Contenu
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu.
     *
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Get order.
     *
     * @return int $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order.
     *
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Get parent.
     *
     * @return Contenu $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent.
     *
     * @param Contenu $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Add child.
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $child
     *
     * @return Contenu
     */
    public function addChild(\HopitalNumerique\ObjetBundle\Entity\Contenu $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child.
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $child
     */
    public function removeChild(\HopitalNumerique\ObjetBundle\Entity\Contenu $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get nbVue.
     *
     * @return int $nbVue
     */
    public function getNbVue()
    {
        return $this->nbVue;
    }

    /**
     * Set nbVue.
     *
     * @param int $nbVue
     */
    public function setNbVue($nbVue)
    {
        $this->nbVue = $nbVue;

        return $this;
    }

    /**
     * Get objet.
     *
     * @return Objet $objet
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Set objet.
     *
     * @param Objet $objet
     *
     * @return Contenu
     */
    public function setObjet(Objet $objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Add type.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $type
     *
     * @return Contenu
     */
    public function addType(\HopitalNumerique\ReferenceBundle\Entity\Reference $type)
    {
        $this->types[] = $type;

        return $this;
    }

    /**
     * Remove type.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $type
     */
    public function removeType(\HopitalNumerique\ReferenceBundle\Entity\Reference $type)
    {
        $this->types->removeElement($type);
    }

    /**
     * Remove all types.
     */
    public function removeTypes()
    {
        $this->types = new \Doctrine\Common\Collections\ArrayCollection();

        return $this;
    }

    /**
     * Get types.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Get types ID.
     *
     * @return array<integer>
     */
    public function getTypeIds()
    {
        $typeIds = [];

        foreach ($this->types as $type) {
            $typeIds[] = $type->getId();
        }

        return $typeIds;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return Contenu
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation.
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateModification.
     *
     * @param \DateTime $dateModification
     *
     * @return Objet
     */
    public function setDateModification($dateModification)
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    /**
     * Get dateModification.
     *
     * @return \DateTime
     */
    public function getDateModification()
    {
        return $this->dateModification;
    }

    /**
     * Get consultations.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $consultations
     */
    public function getConsultations()
    {
        return $this->consultations;
    }

    /**
     * Set consultations.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $consultations
     *
     * @return Objet
     */
    public function setConsultations(\Doctrine\Common\Collections\ArrayCollection $consultations)
    {
        $this->consultations = $consultations;

        return $this;
    }

    /**
     * Get listeCommentaires.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $listeCommentaires
     */
    public function getListeCommentaires()
    {
        return $this->listeCommentaires;
    }

    /**
     * Set listeCommentaires.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $listeCommentaires
     *
     * @return Objet
     */
    public function setListeCommentaires(\Doctrine\Common\Collections\ArrayCollection $listeCommentaires)
    {
        $this->listeCommentaires = $listeCommentaires;

        return $this;
    }

    /**
     * Get listeNotes.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $listeNotes
     */
    public function getListeNotes()
    {
        return $this->listeNotes;
    }

    /**
     * Set listeNotes.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $listeNotes
     *
     * @return Objet
     */
    public function setListeNotes(\Doctrine\Common\Collections\ArrayCollection $listeNotes)
    {
        $this->listeNotes = $listeNotes;

        return $this;
    }

    /**
     * Add objet.
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
     * Remove objet.
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
     * Get objets.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObjets()
    {
        return $this->objets;
    }

    /**
     * Set objets.
     *
     * @param array $objets
     *
     * @return Contenu
     */
    public function setObjets($objets)
    {
        $this->objets = $objets;

        return $this;
    }

    /**
     * Add domaine.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine
     *
     * @return Contenu
     */
    public function addDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        $this->domaines[] = $domaine;

        return $this;
    }

    /**
     * Remove domaine.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine
     */
    public function removeDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        $this->domaines->removeElement($domaine);
    }

    /**
     * Remove all domaines.
     */
    public function removeDomaines()
    {
        $this->domaines = new \Doctrine\Common\Collections\ArrayCollection();

        return $this;
    }

    /**
     * Get domaines.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /**
     * Retourne si le contenu possède ce domaine.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     *
     * @return bool Si domaine
     */
    public function hasDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        foreach ($this->domaines as $contenuDomaine) {
            if ($contenuDomaine->getId() === $domaine->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->titre;
    }

    /**
     * Retourne si l'objet est un point dur.
     *
     * @return bool Si point dur
     */
    public function isPointDur()
    {
        if ($this->objet->isPointDur()) {
            return true;
        }

        foreach ($this->types as $type) {
            if ($type->getId() === Reference::CATEGORIE_OBJET_POINT_DUR_ID) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne les libellés des types.
     *
     * @return array<string> Libellés
     */
    public function getTypeLabels()
    {
        $typeLabels = [];

        foreach ($this->types as $type) {
            $typeLabels[] = $type->getLibelle();
        }

        return $typeLabels;
    }

    /**
     * Retourne les domaines du contenu (ceux de l'objet si aucun objet lié directement au contenu).
     *
     * @return array<\HopitalNumerique\DomaineBundle\Entity\Domaine> Domaines
     */
    public function getRealDomaines()
    {
        if (null !== $this->domaines && count($this->domaines) > 0) {
            return $this->domaines;
        }

        return $this->objet->getDomaines();
    }

    /**
     * Retourne le préfixe du contenu.
     *
     * @return string Préfixe
     */
    public function getPrefix()
    {
        return $this->getPrefixFromContenu($this);
    }

    /**
     * Retourne le préfixe d'un contenu.
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $contenu Contenu
     * @param string                                       $prefix  Préfixe
     *
     * @return string Préfixe
     */
    private function getPrefixFromContenu(Contenu $contenu = null, $prefix = '')
    {
        if (is_null($contenu)) {
            return $prefix;
        }

        return $this->getPrefixFromContenu($contenu->getParent(), $contenu->getOrder() . '.' . $prefix);
    }

    /**
     * Retourne si le contenu a un... contenu.
     *
     * @return bool Si contenu
     */
    public function hasContenu()
    {
        return '' != $this->getContenu();
    }
}
