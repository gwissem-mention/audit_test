<?php

namespace HopitalNumerique\ForumBundle\Entity;

use CCDNForum\ForumBundle\Entity\Forum as BaseForum;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Forum extends BaseForum
{
    const FORUM_COMMUNAUTE_DE_PRATIQUES_ID = 13;
    const FORUM_PUBLIC_ID = 1;

    /**
     * @var Domaine
     */
    private $domain;

    public function getId()
    {
        return $this->id;
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

    public function getReadAuthorisedRoles()
    {
        return array_values(parent::getReadAuthorisedRoles());
    }

    /**
     * @return Domaine
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param Domaine $domain
     *
     * @return Forum
     */
    public function setDomain(Domaine $domain)
    {
        $this->domain = $domain;

        return $this;
    }
}
