<?php
namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")
     */
    private $user;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Objet", inversedBy="consultations")
     * @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id", onDelete="CASCADE")
     */
    private $objet;

    /**
     * @var integer
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
     * @ORM\Column(name="cln_date_last_consulted", type="datetime", options = {"comment" = "Date de dernière consultation de l objet par l user"})
     */
    private $dateLastConsulted;

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
    public function setObjet(\HopitalNumerique\ObjetBundle\Entity\Objet $objet)
    {
        $this->objet = $objet;
    }

    /**
     * Get contenu
     *
     * @return \HopitalNumerique\ObjetBundle\Entity\Contenu $contenu
     */
    public function getContenu()
    {
        return $this->contenu;
    }
    
    /**
     * Set contenu
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $contenu
     */
    public function setContenu(\HopitalNumerique\ObjetBundle\Entity\Contenu $contenu)
    {
        $this->contenu = $contenu;
    }
    
    /**
     * Get user
     *
     * @return \HopitalNumerique\UserBundle\Entity\User $user
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get dateLastConsulted
     *
     * @return DateTime $dateLastConsulted
     */
    public function getDateLastConsulted()
    {
        return $this->dateLastConsulted;
    }
    
    /**
     * Set dateLastConsulted
     *
     * @param DateTime $dateLastConsulted
     */
    public function setDateLastConsulted($dateLastConsulted)
    {
        $this->dateLastConsulted = $dateLastConsulted;
    }

    /**
     * Set domaine
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine
     * @return Consultation
     */
    public function setDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine = null)
    {
        $this->domaine = $domaine;

        return $this;
    }

    /**
     * Get domaine
     *
     * @return \HopitalNumerique\DomaineBundle\Entity\Domaine 
     */
    public function getDomaine()
    {
        return $this->domaine;
    }
}
