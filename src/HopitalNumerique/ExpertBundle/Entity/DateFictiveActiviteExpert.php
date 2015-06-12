<?php

namespace HopitalNumerique\ExpertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DateFictiveActiviteExpert
 *
 * @ORM\Table(name="hn_expert_activite_date_fictive")
 * @ORM\Entity
 */
class DateFictiveActiviteExpert
{
    /**
     * @var integer
     *
     * @ORM\Column(name="eadf_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="eadf_date", type="datetime")
     */
    protected $date;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="ActiviteExpert", inversedBy="dateFictives")
     * @ORM\JoinColumn(name="eadf_activite", referencedColumnName="exp_id", onDelete="CASCADE")
     */
    protected $activite;

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
     * @return DateFictiveActiviteExpert
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
     * Set activite
     *
     * @param \HopitalNumerique\ExpertBundle\Entity\ActiviteExpert $activite
     * @return DateFictiveActiviteExpert
     */
    public function setActivite(\HopitalNumerique\ExpertBundle\Entity\ActiviteExpert $activite = null)
    {
        $this->activite = $activite;

        return $this;
    }

    /**
     * Get activite
     *
     * @return \HopitalNumerique\ExpertBundle\Entity\ActiviteExpert 
     */
    public function getActivite()
    {
        return $this->activite;
    }
}
