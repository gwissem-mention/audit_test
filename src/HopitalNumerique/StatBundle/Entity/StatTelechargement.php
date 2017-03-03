<?php

namespace HopitalNumerique\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatTelechargement.
 *
 * @ORM\Table(name="hn_statistiques_telechargement")
 * @ORM\Entity
 */
class StatTelechargement
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
     * @var int
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Objet")
     * @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id", nullable=true, onDelete="CASCADE")
     * )
     */
    protected $objet;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="stat_session_id", type="text", nullable=true)
     */
    protected $sessionId;

    /**
     * @var string
     *
     * @ORM\Column(name="stat_file_name", type="text", nullable=true)
     */
    protected $fileName;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return StatTelechargement
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Set dateNow.
     *
     * @param \DateTime $date
     *
     * @return StatTelechargement
     */
    public function setDateNow()
    {
        $this->date = new \DateTime();

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get references.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObjet()
    {
        return $this->references;
    }

    /**
     * Set references.
     *
     * @param array(Objet) $objets
     *
     * @return StatTelechargement
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Set user.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     *
     * @return StatTelechargement
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \HopitalNumerique\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set sessionId.
     *
     * @param string $sessionId
     *
     * @return StatTelechargement
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get sessionId.
     *
     * @return string
     */
    public function getsessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set fileName.
     *
     * @param string $fileName
     *
     * @return StatTelechargement
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
}
