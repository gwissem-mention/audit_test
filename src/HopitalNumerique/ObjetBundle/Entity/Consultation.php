<?php
namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Consultation
 *
 * @ORM\Table("hn_objet_consultation")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\ConsultationRepository")
 */
class Consultation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="cln_id", type="integer", options = {"comment" = "ID de la consultation"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")
     */
    private $user;
    
    /**
     * @var Objet
     *
     * @ORM\ManyToOne(targetEntity="Objet", inversedBy="consultations")
     * @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id", onDelete="CASCADE")
     */
    private $objet;

    /**
     * @var Contenu
     *
     * @ORM\ManyToOne(targetEntity="Contenu", inversedBy="consultations")
     * @ORM\JoinColumn(name="con_id", referencedColumnName="con_id", onDelete="CASCADE", nullable=true)
     */
    private $contenu;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id")
     */
    protected $domaine;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cln_date_last_consulted", type="datetime")
     */
    private $dateLastConsulted;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_session_id", type="text", nullable=true)
     */
    protected $sessionId;
    
    public function __construct()
    {
        $this->dateLastConsulted = new \DateTime();
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
     * Get contenu
     *
     * @return Contenu $contenu
     */
    public function getContenu()
    {
        return $this->contenu;
    }
    
    /**
     * Set contenu
     *
     * @param Contenu $contenu
     */
    public function setContenu(Contenu $contenu)
    {
        $this->contenu = $contenu;
    }
    
    /**
     * Get user
     *
     * @return User $user
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set user
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get dateLastConsulted
     *
     * @return \DateTime $dateLastConsulted
     */
    public function getDateLastConsulted()
    {
        return $this->dateLastConsulted;
    }
    
    /**
     * Set dateLastConsulted
     *
     * @param \DateTime $dateLastConsulted
     */
    public function setDateLastConsulted($dateLastConsulted)
    {
        $this->dateLastConsulted = $dateLastConsulted;
    }

    /**
     * Set domaine
     *
     * @param Domaine $domaine
     *
     * @return Consultation
     */
    public function setDomaine(Domaine $domaine = null)
    {
        $this->domaine = $domaine;

        return $this;
    }

    /**
     * Get domaine
     *
     * @return Domaine
     */
    public function getDomaine()
    {
        return $this->domaine;
    }
    
    /**
     * Set sessionId
     *
     * @param string $sessionId
     *
     * @return Consultation
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }
    
    /**
     * Get sessionId
     *
     * @return string
     */
    public function getsessionId()
    {
        return $this->sessionId;
    }
}
