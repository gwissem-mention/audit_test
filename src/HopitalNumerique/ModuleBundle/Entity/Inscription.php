<?php

namespace HopitalNumerique\ModuleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
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
     *
     * @GRID\Column(field="user.nom", options = {"comment" = "Utilisateur inscrit"})
     */
    protected $user;
    
    /**
     * @var string
     *
     * @ORM\Column(name="insc_commentaire", type="text", nullable=true)
     */
    private $commentaire;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat_inscription", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="etatInscription.libelle", nullable=true, options = {"comment" = "Etat de l'inscription pointant sur la table de REFERENCE avec le code STATUT_FORMATION"})
     */
    protected $etatInscription;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat_participation", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="etatParticipation.libelle", nullable=true, options = {"comment" = "Etat de la participation pointant sur la table de REFERENCE avec le code STATUT_FORMATION"})
     */
    protected $etatParticipation;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat_evaluation", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="etatEvaluation.libelle", nullable=true, options = {"comment" = "Etat de l'évaluation pointant sur la table de REFERENCE avec le code STATUT_EVAL_FORMATION"})
     */
    protected $etatEvaluation;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->etatInscription   = 406;
        $this->etatParticipation = 410;
        $this->etatEvaluation    = 413;
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
}
