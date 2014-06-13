<?php

namespace HopitalNumerique\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
//Asserts Stuff
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * RefusCandidature
 *
 * @ORM\Table("hn_user_refus_candidature")
 * @ORM\Entity(repositoryClass="HopitalNumerique\UserBundle\Repository\RefusCandidatureRepository")
 */
class RefusCandidature
{
    /**
     * @var integer
     *
     * @ORM\Column(name="urc_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", inversedBy="refusCandidature")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")
     * @GRID\Column(field="user.username")
     */
    private $user;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_id_orgine_refus", referencedColumnName="usr_id", onDelete="CASCADE")
     */
    private $userOrigineRefus;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire", inversedBy="refusCandidature")
     * @ORM\JoinColumn(name="qst_id", referencedColumnName="qst_id")
     */
    protected $questionnaire;

    /**
     * @var string
     *
     * @ORM\Column(name="urc_motif_refus", type="text", options = {"comment" = "Motif de refus de la candidature"})
     */
    private $motifRefus;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="urc_date_refus", type="datetime", options = {"comment" = "Date de refus de la candidature"})
     */
    private $dateRefus;


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
     * Set motifRefus
     *
     * @param string $motifRefus
     * @return RefusCandidature
     */
    public function setMotifRefus($motifRefus)
    {
        $this->motifRefus = $motifRefus;

        return $this;
    }

    /**
     * Get motifRefus
     *
     * @return string 
     */
    public function getMotifRefus()
    {
        return $this->motifRefus;
    }

    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return RefusCandidature
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
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return RefusCandidature
     */
    public function setUserOrigineRefus(\HopitalNumerique\UserBundle\Entity\User $user = null)
    {
        $this->userOrigineRefus = $user;
    
        return $this;
    }
    
    /**
     * Get user
     *
     * @return \HopitalNumerique\UserBundle\Entity\User
     */
    public function getUserOrigineRefus()
    {
        return $this->userOrigineRefus;
    }
    
    /**
     * Get questionnaire
     *
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }
    
    /**
     * Set ordre
     * @param Questionnaire $questionnaire
     *
     * @return RefusCandidature this
     */
    public function setQuestionnaire( Questionnaire $questionnaire )
    {
        $this->questionnaire = $questionnaire;
    
        return $this;
    }
    
    /**
     * Set dateRefus
     *
     * @param \DateTime $dateRefus
     * @return RefusCandidature
     */
    public function setDateRefus($dateRefus)
    {
        $this->dateRefus = $dateRefus;
    
        return $this;
    }
    
    /**
     * Get dateRefus
     *
     * @return \DateTime
     */
    public function getDateRefus()
    {
        return $this->dateRefus;
    }
}
