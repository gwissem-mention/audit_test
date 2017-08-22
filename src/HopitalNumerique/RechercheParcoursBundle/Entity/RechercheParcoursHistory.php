<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * History for "parcoursGestion".
 *
 * @ORM\Table(name="hn_recherche_recherche_parcours_history")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursHistoryRepository")
 */
class RechercheParcoursHistory
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var RechercheParcoursGestion
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion")
     * @ORM\JoinColumn(name="parcours_id", referencedColumnName="rrpg_id", onDelete="CASCADE")
     */
    private $parcoursGestion;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $userName;

    /**
     * History date and time.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $dateTime;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $notify;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $updateReason;


    public function __construct()
    {
        $this->dateTime = new \DateTime();
    }

    /**
     * Get Guided Search.
     *
     * @return RechercheParcoursGestion
     */
    public function getRechercheParcoursGestion()
    {
        return $this->parcoursGestion;
    }

    /**
     * Set Guided Search.
     *
     * @param RechercheParcoursGestion $parcoursGestion
     *
     * @return RechercheParcoursHistory
     */
    public function setParcoursGestion(RechercheParcoursGestion $parcoursGestion)
    {
        $this->parcoursGestion = $parcoursGestion;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set username.
     *
     * @param string $userName
     *
     * @return RechercheParcoursHistory
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get entry date and time.
     *
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @return bool
     */
    public function isNotify()
    {
        return $this->notify;
    }

    /**
     * @param $notify
     *
     * @return RechercheParcoursHistory
     */
    public function setNotify($notify)
    {
        $this->notify = $notify;

        return $this;
    }

    /**
     * @param $updateReason
     *
     * @return RechercheParcoursHistory
     */
    public function setReason($updateReason)
    {
        $this->updateReason = $updateReason;

        return $this;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->updateReason;
    }

}
