<?php

namespace HopitalNumerique\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="HopitalNumerique\FichierBundle\Repository\FileRepository")
 * @ORM\Table(name="hn_file")
 */
class File
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $clientName;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $active = false;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $owner;

    /**
     * File constructor.
     *
     * @param string $clientName
     * @param string $name
     * @param User $owner
     */
    public function __construct($clientName, $name, User $owner)
    {
        $this->clientName = $clientName;
        $this->name = $name;
        $this->owner = $owner;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * @param string $clientName
     *
     * @return File
     */
    public function setClientName($clientName)
    {
        $this->clientName = $clientName;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return File
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return File
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
