<?php

namespace HopitalNumerique\CoreBundle\Entity\ObjectIdentity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Util\ClassUtils;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="HopitalNumerique\CoreBundle\Repository\ObjectIdentity\SubscriptionRepository")
 * @ORM\Table(name="object_identity_subscription")
 */
class Subscription
{
    /**
     * @var ObjectIdentity
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ObjectIdentity", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $objectIdentity;

    /**
     * @var User
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $subscribedAt;

    /**
     * Subscription constructor.
     *
     * @param ObjectIdentity $objectIdentity
     * @param User $user
     */
    public function __construct(ObjectIdentity $objectIdentity, User $user)
    {
        $this->objectIdentity = $objectIdentity;
        $this->user = $user;
        $this->subscribedAt = new \DateTime();
    }

    /**
     * @return ObjectIdentity
     */
    public function getObjectIdentity()
    {
        return $this->objectIdentity;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return \DateTime
     */
    public function getSubscribedAt()
    {
        return $this->subscribedAt;
    }
}
