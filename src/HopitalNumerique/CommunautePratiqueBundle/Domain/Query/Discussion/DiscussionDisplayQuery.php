<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion;

use HopitalNumerique\UserBundle\Entity\User;
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
     * @var bool $displayAll
     */
    public $displayAll = false;

    /**
     * @param User|null $user
     *
     * @return DiscussionDisplayQuery
     */
    public static function createPublicDiscussionQuery(Discussion $discussion, User $user = null)
    {
        $query = new self();
        $query->user = $user;
        $query->discussion = $discussion;

        return $query;
    }

    /**
     *
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
