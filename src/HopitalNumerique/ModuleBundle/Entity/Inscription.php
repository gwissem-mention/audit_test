<?php

namespace HopitalNumerique\ModuleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Inscription
 *
 * @ORM\Table(name="hn_module_session_inscription")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ModuleBundle\Repository\InscriptionRepository")
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Inscription
{
    /**
     * @var integer
     *
     * @ORM\Column(name="insc_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Session", inversedBy="inscriptions")
     * @ORM\JoinColumn(name="ses_session", referencedColumnName="ses_id")
     */
    protected $session;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_participant", referencedColumnName="usr_id", nullable=true)
     * @ORM\OrderBy({"nom" = "ASC", "prenom" = "ASC"})
     *
     * @GRID\Column(field="user.nom")
     */
    protected $user;
    
    /**
     * @var string
     *
     * @ORM\Column(name="insc_commentaire", type="text", nullable=true)
     */
    private $commentaire;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="insc_total", type="integer", nullable=true)
     */
    private $total;

    /**
     * @var integer
     *
     * @ORM\Column(name="insc_supplement", type="integer", nullable=true)
     */
    private $supplement;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="insc_date_inscription", type="datetime")
     */
    private $dateInscription;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat_inscription", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="etatInscription.libelle")
     */
    protected $etatInscription;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat_participation", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="etatParticipation.libelle")
     */
    protected $etatParticipation;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat_evaluation", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="etatEvaluation.libelle")
     */
    protected $etatEvaluation;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat_remboursement", referencedColumnName="ref_id", nullable=true)
     */
    protected $etatRemboursement;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\PaiementBundle\Entity\Facture", cascade={"persist"}, inversedBy="formations")
     * @ORM\JoinColumn(name="fac_id", referencedColumnName="fac_id", nullable=true)
     */
    protected $facture;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->etatInscription   = 406;
        $this->etatParticipation = 410;
        $this->etatEvaluation    = 27;
        $this->etatRemboursement = null;
        $this->total             = null;
        $this->supplement        = null;
        $this->facture           = null;
        $this->dateInscription   = new \DateTime();
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

    public function getSession()
    {
        return $this->session;
    }
    
    public function getSessionId()
    {
        return $this->session->getId();
    }
    
    public function setSession( Session $session )
    {
        $this->session = $session;
    }
    
    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return Inscription
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string 
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }
    
    /**
     * Get total
     *
     * @return integer $total
     */
    public function getTotal()
    {
        return $this->total;
    }
    
    /**
     * Set total
     *
     * @param integer $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }
    
    /**
     * Get supplement
     *
     * @return integer $supplement
     */
    public function getSupplement()
    {
        return $this->supplement;
    }
    
    /**
     * Set supplement
     *
     * @param integer $supplement
     */
    public function setSupplement($supplement)
    {
        $this->supplement = $supplement;
        return $this;
    }
    
    /**
     * Get dateInscription
     *
     * @return DateTime $dateInscription
     */
    public function getDateInscription()
    {
        return $this->dateInscription;
    }
    
    /**
     * Set dateInscription
     *
     * @param DateTime $dateInscription
     */
    public function setDateInscription($dateInscription)
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }

    /**
     * Get dateInscription string
     *
     * @return string
     */
    public function getDateInscriptionString()
    {
        return $this->dateInscription->format('d/m/Y');
    }

    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return Reponse
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user = null)
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
     * Set etatInscription
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $etatInscription
     */
    public function setEtatInscription($etatInscription)
    {
        if($etatInscription instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->etatInscription = $etatInscription;
        else
            $this->etatInscription = null;
    }
    
    /**
     * Get etatInscription
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $etatInscription
     */
    public function getEtatInscription()
    {
        return $this->etatInscription;
    }
    
    /**
     * Get etatInscription Id
     *
     * @return int idEtatInscription
     */
    public function getEtatInscriptionId()
    {
        return $this->etatInscription->getId();
    }

    /**
     * Inscrit ?
     *
     * @return boolean Inscrit
     */
    public function isInscrit()
    {
        if(407 === $this->etatInscription->getId())
            return true;

        return false;
    }
    
    /**
     * Set etatParticipation
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $etatParticipation
     */
    public function setEtatParticipation($etatParticipation)
    {
        if($etatParticipation instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->etatParticipation = $etatParticipation;
        else
            $this->etatParticipation = null;
    }
    /**
     * Get etatParticipation
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $etatParticipation
     */
    public function getEtatParticipation()
    {
        return $this->etatParticipation;
    }
    
    /**
     * Set etatEvaluation
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $etatEvaluation
     */
    public function setEtatEvaluation($etatEvaluation)
    {
        if($etatEvaluation instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->etatEvaluation = $etatEvaluation;
        else
            $this->etatEvaluation = null;
    }
    
    /**
     * Get etatEvaluation
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $etatEvaluation
     */
    public function getEtatEvaluation()
    {
        return $this->etatEvaluation;
    }

    /**
     * Get etatRemboursement
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $etatRemboursement
     */
    public function getEtatRemboursement()
    {
        return $this->etatRemboursement;
    }
    
    /**
     * Set etatRemboursement
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $etatRemboursement
     */
    public function setEtatRemboursement(\HopitalNumerique\ReferenceBundle\Entity\Reference $etatRemboursement)
    {
        $this->etatRemboursement = $etatRemboursement;
        return $this;
    }

    /**
     * Get facture
     *
     * @return \HopitalNumerique\PaiementBundle\Entity\Facture $facture
     */
    public function getFacture()
    {
        return $this->facture;
    }
    
    /**
     * Set facture
     *
     * @param \HopitalNumerique\PaiementBundle\Entity\Facture $facture
     */
    public function setFacture(\HopitalNumerique\PaiementBundle\Entity\Facture $facture)
    {
        $this->facture = $facture;
        return $this;
    }
}
