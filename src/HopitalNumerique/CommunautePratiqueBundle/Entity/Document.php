<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nodevo\ToolsBundle\Tools\Fichier;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Entité Document.
 *
 * @ORM\Entity()
 * @ORM\Table(name="hn_communautepratique_document")
 * @ORM\HasLifecycleCallbacks()
 */
class Document
{
    /**
     * @var integer Taille maximale du fichier en Mo
     */
    const MAX_SIZE = 5;


    /**
     * @var array<string, string> Icones des documents par extension de fichier
     */
    public static $ICONES_BY_EXTENSION = array
    (
        'doc' => '<em class="icon-file-doc"></em>',
        'docx' => '<em class="icon-file-docx"></em>',
        'jpg' => '<em class="icon-file-jpg"></em>',
        'jpeg' => '<em class="icon-file-jpg"></em>',
        'pdf' => '<em class="icon-file-pdf"></em>',
        'xls' => '<em class="icon-file-xls"></em>',
        'xlsx' => '<em class="icon-file-xlsx"></em>'
    );


    /**
     * @var integer
     *
     * @ORM\Column(name="doc_id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="doc_nom", type="string", nullable=false, length=255)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="doc_libelle", type="string", nullable=false, length=255)
     * @Assert\NotBlank()
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="doc_size", type="integer", nullable=false, options={"comment"="Taille du fichier en octet"})
     * @Assert\NotNull()
     */
    private $size;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="doc_date_creation", type="datetime", nullable=false)
     */
    private $dateCreation;

    /**
     * @var \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe
     *
     * @ORM\ManyToOne(targetEntity="Groupe", inversedBy="documents")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="group_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $groupe;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User", inversedBy="communautePratiqueDocuments")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Fiche", mappedBy="documents")
     */
    private $fiches;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->fiches = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set nom
     *
     * @param string $nom
     * @return Document
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return Document
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
     * Set size
     *
     * @param integer $size
     * @return Document
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Document
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
     * Set groupe
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe
     * @return Document
     */
    public function setGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return Document
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \HopitalNumerique\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add fiches
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiches
     * @return Document
     */
    public function addFiche(\HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiches)
    {
        $this->fiches[] = $fiches;

        return $this;
    }

    /**
     * Remove fiches
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiches
     */
    public function removeFiche(\HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiches)
    {
        $this->fiches->removeElement($fiches);
    }

    /**
     * Get fiches
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiches()
    {
        return $this->fiches;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->libelle;
    }


    /**
     * Retourne le chemin du dossier.
     * 
     * @return string Path
     */
    private function getPath()
    {
        return 'files'.DIRECTORY_SEPARATOR.'communaute-de-pratiques'.DIRECTORY_SEPARATOR.'documents';
    }

    /**
     * Retourne le chemin du fichier.
     * 
     * @return string Pathname
     */
    public function getPathname()
    {
        return $this->getPath().DIRECTORY_SEPARATOR.$this->nom;
    }


    /**
     * @ORM\PreRemove()
     */
    public function preRemove()
    {
        $this->deleteFichier();
    }

    /**
     * Supprime le fichier du serveur.
     */
    private function deleteFichier()
    {
        if ( file_exists( '..'.DIRECTORY_SEPARATOR.$this->getPathname() ) )
        {
            unlink('..'.DIRECTORY_SEPARATOR.$this->getPathname());
        }
    }


    /**
     * Retourne l'extension du document.
     *
     * @return string|NULL Extension
     */
    public function getExtension()
    {
        return Fichier::getExtensionFromFile($this->nom);
    }


    /**
     * Retourne si le document est une image.
     *
     * @return boolean VRAI si image
     */
    public function isImage()
    {
        return (in_array(Fichier::getExtensionFromFile($this->nom), array('jpg', 'jpeg', 'png', 'gif')));
    }

    /**
     * Retourne l'icône du document en HTML.
     * 
     * @return string Icône
     */
    public function getIconeHtml()
    {
        if ( isset( self::$ICONES_BY_EXTENSION[ $this->getExtension() ] ) )
        {
            return self::$ICONES_BY_EXTENSION[$this->getExtension()];
        }

        return '<em class="fa fa-file-o"></em>';
    }

    /**
     * Retourne le libellé de la taille.
     * 
     * @return string Taille
     */
    public function getSizeLibelle()
    {
        return ($this->size >= 1024 * 1024 ? number_format( $this->size / ( 1024 * 1024 ), 2, ',', ' ' ).' Mo' : ($this->size >= 1024 ? number_format( $this->size / 1024, 2, ',', ' ' ).' Ko' : $this->size.' o') );
    }
}
