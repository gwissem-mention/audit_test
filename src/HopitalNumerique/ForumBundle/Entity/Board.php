<?php

namespace HopitalNumerique\ForumBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use CCDNForum\ForumBundle\Entity\Board as BaseBoard;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentityDisplayableInterface;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Board extends BaseBoard implements ObjectIdentityDisplayableInterface
{
    const BOARD_MHN_ID = 46;
    const BOARD_RSE_ID = 45;

    /**
     * @var bool
     */
    private $piecesJointesAutorisees;

    /**
     * @var ArrayCollection
     */
    private $subscriptions;

    /**
     * @param SecurityContextInterface $securityContext
     *
     * @return bool
     */
    public function isAuthorisedToReplyToTopic(SecurityContextInterface $securityContext)
    {
        if (0 == count($this->topicReplyAuthorisedRoles)) {
            return true;
        }

        foreach ($this->topicReplyAuthorisedRoles as $role) {
            if (!$securityContext->getToken()->getUser() instanceof User) {
                if ('ROLE_ANONYME_10' === $role) {
                    return true;
                }
            } elseif ($securityContext->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SecurityContextInterface $securityContext
     *
     * @return bool
     */
    public function isAuthorisedToCreateTopic(SecurityContextInterface $securityContext)
    {
        if (0 == count($this->topicCreateAuthorisedRoles)) {
            return true;
        }

        foreach ($this->topicCreateAuthorisedRoles as $role) {
            if (!$securityContext->getToken()->getUser() instanceof User) {
                if ('ROLE_ANONYME_10' === $role) {
                    return true;
                }
            } elseif ($securityContext->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SecurityContextInterface $securityContext
     *
     * @return bool
     */
    public function isAuthorisedToRead(SecurityContextInterface $securityContext)
    {
        if (0 == count($this->readAuthorisedRoles)) {
            return true;
        }

        foreach ($this->readAuthorisedRoles as $role) {
            if (!$securityContext->getToken()->getUser() instanceof User) {
                if ('ROLE_ANONYME_10' === $role) {
                    return true;
                }
            } elseif ($securityContext->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set piecesJointesAutorisees.
     *
     * @param bool $piecesJointesAutorisees
     *
     * @return Board
     */
    public function setPiecesJointesAutorisees($piecesJointesAutorisees)
    {
        $this->piecesJointesAutorisees = $piecesJointesAutorisees;

        return $this;
    }

    /**
     * Get piecesJointesAutorisees.
     *
     * @return bool
     */
    public function isPiecesJointesAutorisees()
    {
        return $this->piecesJointesAutorisees;
    }

    /**
     * Get subscriptions.
     *
     * @return ArrayCollection
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * Set subscriptions.
     *
     * @param ArrayCollection $subscriptions
     *
     * @return Board
     */
    public function setSubscriptions(ArrayCollection $subscriptions = null)
    {
        $this->subscriptions = $subscriptions;

        return $this;
    }

    /**
     * Add topic.
     *
     * @param Subscription $subscription
     *
     * @return Board
     */
    public function addSubscription(Subscription $subscription)
    {
        $this->subscriptions->add($subscription);

        return $this;
    }

    /**
     * @param Subscription $subscription
     *
     * @return $this
     */
    public function removeSubscription(Subscription $subscription)
    {
        $this->subscriptions->removeElement($subscription);

        return $this;
    }

    public function getReadAuthorisedRoles()
    {
        return array_values(parent::getReadAuthorisedRoles()) ?: [];
    }

    /**
     * @return string
     */
    public function getObjectIdentityTitle()
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getObjectIdentityType()
    {
        return 'board';
    }
}
