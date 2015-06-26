<?php

namespace HopitalNumerique\ExpertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EvenementPresenceExpert
 *
 * @ORM\Table(name="hn_expert_evenement_presence_expert")
 * @ORM\Entity
 */
class EvenementPresenceExpert
{
    /**
     * @var integer
     *
     * @ORM\Column(name="evePE_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="EvenementExpert", inversedBy="experts")
     * @ORM\JoinColumn(name="evePE_evenement", referencedColumnName="eveE_id")
     */
    protected $evenement;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="evePE_date", nullable=true, type="datetime")
     */
    protected $date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="evePE_present", type="boolean")
     */
    protected $present;


    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="evePE_expert", referencedColumnName="usr_id")
     */
    protected $expertConcerne;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->present         = false;
        $this->expertConcernes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set date
     *
     * @param \DateTime $date
     * @return EvenementPresenceExpert
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set present
     *
     * @param boolean $present
     * @return EvenementPresenceExpert
     */
    public function setPresent($present)
    {
        $this->present = $present;

        return $this;
    }

    /**
     * Get present
     *
     * @return boolean 
     */
    public function getPresent()
    {
        return $this->present;
    }

    /**
     * Set evenement
     *
     * @param \HopitalNumerique\ExpertBundle\Entity\EvenementExpert $evenement
     * @return EvenementPresenceExpert
     */
    public function setEvenement(\HopitalNumerique\ExpertBundle\Entity\EvenementExpert $evenement = null)
    {
        $this->evenement = $evenement;

        return $this;
    }

    /**
     * Get evenement
     *
     * @return \HopitalNumerique\ExpertBundle\Entity\EvenementExpert 
     */
    public function getEvenement()
    {
        return $this->evenement;
    }

    /**
     * Set expertConcerne
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $expertConcerne
     * @return EvenementPresenceExpert
     */
    public function setExpertConcerne(\HopitalNumerique\UserBundle\Entity\User $expertConcerne = null)
    {
        $this->expertConcerne = $expertConcerne;

        return $this;
    }

    /**
     * Get expertConcerne
     *
     * @return \HopitalNumerique\UserBundle\Entity\User 
     */
    public function getExpertConcerne()
    {
        return $this->expertConcerne;
    }
}
