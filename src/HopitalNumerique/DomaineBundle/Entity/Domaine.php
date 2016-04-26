<?php

namespace HopitalNumerique\DomaineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Symfony\Component\Validator\Constraints as Assert;

//Tools
use \Nodevo\ToolsBundle\Tools\Chaine;

/**
 * Domaine
 *
 * @ORM\Table(name="hn_domaine")
 * @ORM\Entity(repositoryClass="HopitalNumerique\DomaineBundle\Repository\DomaineRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Domaine
{
    /**
     * @var integer ID du domaine HN
     */
    const DOMAINE_HOPITAL_NUMERIQUE_ID = 1;


    /**
     * @var integer
     *
     * @ORM\Column(name="dom_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="dom_nom", type="string", length=255)
     */
    protected $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="dom_description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="dom_analytics", type="text", nullable=true)
     */
    private $googleAnalytics;

    /**
     * @var string
     *
     * @ORM\Column(name="dom_url", type="string", length=255)
     */
    protected $url;

    /**
     * @Assert\File(
     *     maxSize = "10M",
     *     mimeTypes = { 
     *         "image/gif", 
     *         "image/jpeg", 
     *         "image/png",
     *     },
     *     mimeTypesMessage = "Choisissez un fichier valide (IMAGE)"
     * )
     */
    public $file;
    
    /**
     * @var string
     *
     * @ORM\Column(name="dom_logo", type="string", length=255, nullable=true, options = {"comment" = "Nom du fichier stocké"})
     */
    protected $path;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dom_date_derniere_maj", type="datetime")
     */
    protected $dateLastUpdate;

    /**
     * @ORM\ManyToOne(targetEntity="Template", cascade={"persist"})
     * @ORM\JoinColumn(name="temp_id", referencedColumnName="temp_id")
     * 
     * @GRID\Column(field="template.nom", options = {"comment" = "Type de template à utiliser sur le domaine"})
     */
    protected $template;

    /**
     * @var string
     *
     * @ORM\Column(name="dom_adresse_mail_contact", type="string", length=255)
     */
    protected $adresseMailContact;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\UserBundle\Entity\User", mappedBy="domaines")
     */
    protected $users;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", mappedBy="domaines")
     */
    protected $references;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Objet", mappedBy="domaines")
     */
    protected $objets;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Contenu", mappedBy="domaines")
     */
    private $contenus;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\RechercheBundle\Entity\Requete", mappedBy="domaine")
     */
    protected $requetes;

    /**
     * @var string
     *
     * @ORM\Column(name="dom_homepage", type="text", nullable=true, options = {"comment" = "Texte affiché sur la homepage"})
     */
    protected $homepage;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ForumBundle\Entity\Forum", cascade={"persist"})
     * @ORM\JoinTable(name="hn_domaine_gestions_forum",
     *      joinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $forums;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe", mappedBy="domaine")
     */
    protected $communautePratiqueGroupes;


    /**
     * @var string
     *
     * @ORM\Column(name="url_titre", type="text", nullable=true)
     */
    protected $urlTitre;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users      = new \Doctrine\Common\Collections\ArrayCollection();
        $this->references = new \Doctrine\Common\Collections\ArrayCollection();
        $this->objets     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->contenus = new \Doctrine\Common\Collections\ArrayCollection();
        $this->communautePratiqueGroupes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set url
     *
     * @param string $url
     * @return Domaine
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Domaine
     */
    public function setUrlTitre($urlTitre)
    {
    	$this->urlTitre = $urlTitre;
    
    	return $this;
    }
    
    /**
     * Get url
     *
     * @return string
     */
    public function getUrlTitre()
    {
    	return $this->urlTitre;
    }
    
    /**
     * Set adresseMailContact
     *
     * @param string $adresseMailContact
     * @return Domaine
     */
    public function setAdresseMailContact($adresseMailContact)
    {
        $this->adresseMailContact = $adresseMailContact;

        return $this;
    }

    /**
     * Get adresseMailContact
     *
     * @return string 
     */
    public function getAdresseMailContact()
    {
        return $this->adresseMailContact;
    }

    /**
     * Set template
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Template $template
     * @return Domaine
     */
    public function setTemplate(\HopitalNumerique\DomaineBundle\Entity\Template $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return \HopitalNumerique\DomaineBundle\Entity\Template 
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Add users
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $users
     * @return Domaine
     */
    public function addUser(\HopitalNumerique\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $users
     */
    public function removeUser(\HopitalNumerique\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add references
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $references
     * @return Domaine
     */
    public function addReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $references)
    {
        $this->references[] = $references;

        return $this;
    }

    /**
     * Remove references
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $references
     */
    public function removeReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $references)
    {
        $this->references->removeElement($references);
    }

    /**
     * Get references
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Add objets
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $objets
     * @return Domaine
     */
    public function addObjet(\HopitalNumerique\ObjetBundle\Entity\Objet $objets)
    {
        $this->objets[] = $objets;

        return $this;
    }

    /**
     * Remove objets
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $objets
     */
    public function removeObjet(\HopitalNumerique\ObjetBundle\Entity\Objet $objets)
    {
        $this->objets->removeElement($objets);
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

    /**
     * Add contenus
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $contenus
     *
     * @return Domaine
     */
    public function addContenus(\HopitalNumerique\ObjetBundle\Entity\Contenu $contenus)
    {
        $this->contenus[] = $contenus;

        return $this;
    }

    /**
     * Remove contenus
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $contenus
     */
    public function removeContenus(\HopitalNumerique\ObjetBundle\Entity\Contenu $contenus)
    {
        $this->contenus->removeElement($contenus);
    }

    /**
     * Get contenus
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContenus()
    {
        return $this->contenus;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Domaine
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
     * Set dateLastUpdate
     *
     * @param \DateTime $dateLastUpdate
     * @return Domaine
     */
    public function setDateLastUpdate($dateLastUpdate)
    {
        $this->dateLastUpdate = $dateLastUpdate;

        return $this;
    }

    /**
     * Get dateLastUpdate
     *
     * @return \DateTime 
     */
    public function getDateLastUpdate()
    {
        return $this->dateLastUpdate;
    }
    // ----------------------------------------
    // --- Gestion de l'upload des fichiers ---
    // ----------------------------------------
    
    /**
     * Set path
     *
     * @param string $path
     * @return Domaine
     */
    public function setPath($path)
    {
        if( is_null($path) && file_exists($this->getAbsolutePath()) )
            unlink($this->getAbsolutePath());
    
        $this->path = $path;
    
        return $this;
    }
    
    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }
    
    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }
    
    public function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __WEB_DIRECTORY__.'/'.$this->getUploadDir();
    }
    
    public function getUploadDir()
    {
        return 'medias/Domaines';
    }
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file){
            //delete Old File
            if ( file_exists($this->getAbsolutePath()) )
                unlink($this->getAbsolutePath());

            $tool = new Chaine( $this->getNom() );
            $nomFichier = $tool->minifie();

            $this->path = round(microtime(true) * 1000) . '_' . $nomFichier . '.png';
        }
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file)
            return;
    
        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si
        // erreur il y a
        $this->file->move($this->getUploadRootDir(), $this->path);
    
        unset($this->file);
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $file = $this->getAbsolutePath();
    
        if (file_exists($file) )
            unlink($file);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Domaine
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
     * Add requetes
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\Requete $requetes
     * @return Domaine
     */
    public function addRequete(\HopitalNumerique\RechercheBundle\Entity\Requete $requetes)
    {
        $this->requetes[] = $requetes;

        return $this;
    }

    /**
     * Remove requetes
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\Requete $requetes
     */
    public function removeRequete(\HopitalNumerique\RechercheBundle\Entity\Requete $requetes)
    {
        $this->requetes->removeElement($requetes);
    }

    /**
     * Get requetes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRequetes()
    {
        return $this->requetes;
    }

    /**
     * Add forums
     *
     * @param \HopitalNumerique\ForumBundle\Entity\Forum $forums
     * @return Domaine
     */
    public function addForum(\HopitalNumerique\ForumBundle\Entity\Forum $forums)
    {
        $this->forums[] = $forums;

        return $this;
    }

    /**
     * Remove forums
     *
     * @param \HopitalNumerique\ForumBundle\Entity\Forum $forums
     */
    public function removeForum(\HopitalNumerique\ForumBundle\Entity\Forum $forums)
    {
        $this->forums->removeElement($forums);
    }

    /**
     * Get forums
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getForums()
    {
        return $this->forums;
    }

    /**
     * Set googleAnalytics
     *
     * @param string $googleAnalytics
     * @return Domaine
     */
    public function setGoogleAnalytics($googleAnalytics)
    {
        $this->googleAnalytics = $googleAnalytics;

        return $this;
    }

    /**
     * Get googleAnalytics
     *
     * @return string 
     */
    public function getGoogleAnalytics()
    {
        return $this->googleAnalytics;
    }

    /**
     * Set homepage
     *
     * @param string $homepage
     * @return Domaine
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;

        return $this;
    }

    /**
     * Get homepage
     *
     * @return string 
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * Add communautePratiqueGroupes
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes
     * @return Domaine
     */
    public function addCommunautePratiqueGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes)
    {
        $this->communautePratiqueGroupes[] = $communautePratiqueGroupes;

        return $this;
    }

    /**
     * Remove communautePratiqueGroupes
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes
     */
    public function removeCommunautePratiqueGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes)
    {
        $this->communautePratiqueGroupes->removeElement($communautePratiqueGroupes);
    }

    /**
     * Get communautePratiqueGroupes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCommunautePratiqueGroupes()
    {
        return $this->communautePratiqueGroupes;
    }
    
    
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->nom;
    }

    /**
     * Retourne l'entité comme un tableau.
     *
     * @return array Domaine
     */
    public function __toArray()
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom
        ];
    }

    /**
     * Retourne l'égalité entre deux domaines.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Domaine $domaine Autre domaine
     * @return boolean Si égalité
     */
    public function equals(Domaine $domaine)
    {
        return ($this->id === $domaine->getId());
    }
}
