<?php

namespace HopitalNumerique\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class StatConnections
 *
 * @ORM\Entity
 * @ORM\Table(name="hn_statistics_connections")
 */
class StatConnections
{
    /**
     * @var int
     *
     * @ORM\Column(name="stat_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stat_date", type="datetime")
     */
    protected $date;

    /**
     * @var Domaine
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\DomaineBundle\Entity\Domaine")
     * @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")
     */
    protected $domain;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * StatConnections constructor.
     *
     * @param Domaine $domain
     * @param User $user
     */
    public function __construct(Domaine $domain, User $user)
    {
        $this->date = new \DateTime();
        $this->domain = $domain;
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Domaine
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
