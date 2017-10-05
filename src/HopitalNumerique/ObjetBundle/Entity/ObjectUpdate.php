<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class ObjectUpdate.
 *
 * @ORM\Table(name="hn_object_update")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\ObjectUpdateRepository")
 */
class ObjectUpdate
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Objet
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ObjetBundle\Entity\Objet", cascade={"persist"}, inversedBy="updates")
     * @ORM\JoinColumn(referencedColumnName="obj_id")
     */
    protected $object;


    /**
     * @var Contenu
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ObjetBundle\Entity\Contenu", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="con_id")
     */
    protected $contenu;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $reason;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name= "updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * ObjectUpdate constructor.
     */
    public function __construct()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return Objet
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param Objet $object
     *
     * @return ObjectUpdate
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return Contenu
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * @param Contenu $contenu
     *
     * @return ObjectUpdate
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return ObjectUpdate
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     *
     * @return ObjectUpdate
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return ObjectUpdate
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
