<?php

namespace HopitalNumerique\CartBundle\Entity\Item;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Table(name="hn_cart_item")
 * @ORM\Entity(repositoryClass="HopitalNumerique\CartBundle\Repository\CartItemRepository")
 */
class CartItem extends Item
{
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $owner;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $addedAt;

    /**
     * CartItem constructor.
     *
     * @param $objectType
     * @param $objectId
     * @param User $owner
     * @param Domaine $domain
     */
    public function __construct($objectType, $objectId, User $owner, Domaine $domain = null)
    {
        $this->objectType = $objectType;
        $this->objectId = $objectId;
        $this->owner = $owner;
        $this->domain = $domain;
        $this->addedAt = new \DateTime();
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return \DateTime
     */
    public function getAddedAt()
    {
        return $this->addedAt;
    }

    /**
     * @param User $owner
     *
     * @return CartItem
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

        return $this;
    }
}
