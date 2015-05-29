<?php

namespace HopitalNumerique\ForumBundle\Entity;

use CCDNForum\ForumBundle\Entity\Forum as BaseForum;
use Symfony\Component\Security\Core\SecurityContextInterface;
/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Forum extends BaseForum
{
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param  SecurityContextInterface $securityContext
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