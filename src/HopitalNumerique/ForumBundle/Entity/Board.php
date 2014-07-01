<?php

namespace HopitalNumerique\ForumBundle\Entity;

use CCDNForum\ForumBundle\Entity\Board as BaseBoard;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Board extends BaseBoard
{
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

        foreach ($this->topicReplyAuthorisedRoles as $role) 
        {
            if ("anon."  === $securityContext->getToken()->getUser())
            {
                if ('ROLE_ANONYME_10' === $role) 
                {
                    return true;
                }
            }
            elseif ($securityContext->isGranted($role)) {
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

        foreach ($this->topicCreateAuthorisedRoles as $role) 
        {
            if ("anon."  === $securityContext->getToken()->getUser())
            {
                if ('ROLE_ANONYME_10' === $role) 
                {
                    return true;
                }
            }
            elseif ($securityContext->isGranted($role)) {
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

        foreach ($this->readAuthorisedRoles as $role) 
        {
            if ("anon."  === $securityContext->getToken()->getUser())
            {
                if ('ROLE_ANONYME_10' === $role) 
                {
                    return true;
                }
            }
            elseif ($securityContext->isGranted($role)) {
                return true;
            }
        }

        return false;
    }
}