<?php

namespace HopitalNumerique\ForumBundle\Entity;

use CCDNForum\ForumBundle\Entity\Category as BaseCategory;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Category extends BaseCategory
{
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