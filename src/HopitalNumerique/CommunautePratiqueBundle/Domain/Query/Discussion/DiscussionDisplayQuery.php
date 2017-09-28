<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;

/**
 * Class DiscussionDisplayQuery
 *
 * All info used to query discussions messages, public/private message, access...
 */
class DiscussionDisplayQuery
{
    /**
     * @var Discussion $discussion
     */
    public $discussion;

    /**
     * @var User|null $user
     */
    public $user;

    /**
     * @var Groupe $group
     */
    public $group;

    /**
     * @var Domaine[] $domains
     */
    public $domains;

    /**
     * @var bool $displayAll
     */
    public $displayAll = false;

    /**
     * @param Discussion $discussion
     * @param Domaine[] $domains
     * @param Groupe $group
     * @param User|null $user
     *
     * @return DiscussionDisplayQuery
     */
    public static function createPublicDiscussionQuery(Discussion $discussion, array $domains, Groupe $group = null, User $user = null)
    {
        $query = new self();
        $query->user = $user;
        $query->group = $group;
        $query->domains = $domains;
        $query->discussion = $discussion;

        $query->resolveDisplayAllForGroups();

        return $query;
    }

    /**
     * Display all messages for moderators, admins...
     * Depend of group
     */
    protected function resolveDisplayAllForGroups()
    {
        if (null === $this->user) {
            return;
        }

        foreach ($this->user->getCommunautePratiqueAnimateurGroupes() as $group) {
            if ($this->discussion->getGroups()->contains($group)) {
                $this->displayAll = true;

                return;
            }
        }

        if ($this->user->hasRoleCDPAdmin()) {
            $this->displayAll = true;
        }
    }
}
