<?php

namespace HopitalNumerique\RechercheBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nodevo\ToolsBundle\Tools\Systeme;
use Nodevo\ToolsBundle\Traits\ImageTrait;

/**
 * ExpBesoin
 *
 * @ORM\Table(name="hn_recherche_expbesoin")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheBundle\Repository\ExpBesoinRepository")
 */
class ExpBesoin
{
    use ImageTrait;


    /**
     * @var integer
     *
     * @ORM\Column(name="expb_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ExpBesoinGestion", cascade={"persist"}, inversedBy="expBesoins")
     * @ORM\JoinColumn(name="expbg_id", referencedColumnName="expbg_id", onDelete="CASCADE")
     */
    protected $expBesoinGestion;

    /**
     * @var integer
     *
     * @ORM\Column(name="expb_order", type="smallint", options = {"comment" = "Ordre de la question"})
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="expb_libelle", type="string", length=255)
     */
    protected $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="expbr_description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="expb_image", type="text", nullable=true, length=255)
     */
    protected $image;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $imageFile;


    /**
     * Liste des inscriptions liées au module
     *
     * @var /HopitalNumerique/RechercheBundle/Entity/ExpBesoinReponses
     *
     * @ORM\OneToMany(targetEntity="ExpBesoinReponses", mappedBy="question", cascade={"persist", "remove" })
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $reponses;


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
     * Set libelle
     *
     * @param string $libelle
     * @return ExpBesoin
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
     * Add reponses
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $reponses
     * @return Menu
     */
    public function addReponse(\HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $reponses)
    {
        $this->reponses[] = $reponses;
    
        return $this;
    }

    /**
     * Remove reponses
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $reponses
     */
    public function removeReponse(\HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $reponses)
    {
        $this->reponses->removeElement($reponses);
    }

    /**
     * Get reponses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReponses()
    {
        return $this->reponses;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reponses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ExpBesoin
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set expBesoinGestion
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoinGestion $expBesoinGestion
     * @return ExpBesoin
     */
    public function setExpBesoinGestion(\HopitalNumerique\RechercheBundle\Entity\ExpBesoinGestion $expBesoinGestion = null)
    {
        $this->expBesoinGestion = $expBesoinGestion;

        return $this;
    }

    /**
     * Get expBesoinGestion
     *
     * @return \HopitalNumerique\RechercheBundle\Entity\ExpBesoinGestion 
     */
    public function getExpBesoinGestion()
    {
        return $this->expBesoinGestion;
    }


    /**
     * {@inheritdoc}
     */
    public function getImageUploadDir()
    {
        return 'media'.DIRECTORY_SEPARATOR.'expression-besoin';
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
