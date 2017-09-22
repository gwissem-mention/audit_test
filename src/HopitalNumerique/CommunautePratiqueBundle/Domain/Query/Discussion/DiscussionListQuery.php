<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

class DiscussionListQuery
{
    /**
     * @var Domaine[] $domains
     */
    public $domains;

    /**
     * @var User|null $user
     */
    public $user;

    /**
     * If user is moderator or admin, display all messages
     *
     * @var Groupe[]
     */
    public $displayAllForGroups = [];

    public $displayAll = false;

    /**
     * @var Groupe[] $groups
     */
    public $groups = [];

    /**
     * @param array $domains
     * @param array $groups
     * @param User|null $user
     *
     * @return DiscussionListQuery
     */
    public static function getPublicDiscussion(array $domains, array $groups = [], User $user = null)
    {
        $query = new self();
        $query->domains = $domains;
        $query->user = $user;
        $query->groups = $groups;

        $query->resolveDisplayAllForGroups();

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
            $this->displayAllForGroups[$group->getId()] = $group;
        }

        if ($this->user->hasRoleCDPAdmin()) {
            $this->displayAll = true;
        }
    }
}
