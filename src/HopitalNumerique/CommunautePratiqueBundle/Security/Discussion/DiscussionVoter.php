<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Security\Discussion;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;

/**
 * Class DiscussionVoter
 */
class DiscussionVoter extends Voter
{
    const ACCESS = 'ACCESS';
    const CREATE = 'cdp_discussion_create';
    const REPLY = 'cdp_discussion_reply';
    const MARK_AS_RECOMMENDED = 'mark_as_recommended';
    const COPY_TO_GROUP = 'copy_to_group';
    const DOWNLOAD = 'download';
    const SUBSCRIBE = 'subscribe';
    const MANAGE_DOMAINS = 'manage_domains';
    const REORDER = 'reorder_discussion';
    const SET_AS_PUBLIC = 'set_as_public';

    /**
     * @param string $attribute
     * @param Discussion $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::SET_AS_PUBLIC,
            self::ACCESS,
            self::MANAGE_DOMAINS,
            self::SUBSCRIBE,
            self::REORDER,
            self::DOWNLOAD,
            self::MARK_AS_RECOMMENDED,
            self::COPY_TO_GROUP,
            self::CREATE,
            self::REPLY])
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Discussion $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        switch ($attribute) {
            case self::ACCESS:
                return $this->canAccess($user instanceof User ? $user : null, $subject);
        }

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::REPLY:
                return $this->canAccess($user, $subject);
            case self::SET_AS_PUBLIC:
                return $this->canSetDiscussionAsPublic($user, $subject);
            case self::COPY_TO_GROUP:
                return $this->canCopyToGroup($user, $subject);
            case self::REORDER:
            case self::MARK_AS_RECOMMENDED:
            case self::MANAGE_DOMAINS:
                return $this->canManage($user);
            case self::CREATE:
            case self::DOWNLOAD:
            case self::SUBSCRIBE:
                return $this->canCreate($user);
        }

        return false;
    }

    /**
     * @param User $user
     * @param Discussion $discussion
     *
     * @return bool
     */
    protected function canSetDiscussionAsPublic(User $user, Discussion $discussion)
    {
        if ($this->canManage($user)) {
            return true;
        }

        if ($discussion->getGroups()->count()) {
            foreach ($discussion->getGroups() as $group) {
                if ($group->getAnimateurs()->exists(function ($key, User $animator) use ($user) {
                    return $animator->getId() === $user->getId();
                })) {
                    return true;
                }
            }
        }

        return false;
    }
    /**
     * @param User $user
     * @param Discussion $discussion
     *
     * @return bool
     */
    protected function canCopyToGroup(User $user, Discussion $discussion)
    {
        if ($this->canSetDiscussionAsPublic($user, $discussion)) {
            return true;
        }

        return $user->getCommunautePratiqueAnimateurGroupes()->count() > 0;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canManage(User $user)
    {
        if ($user->hasRoleCDPAdmin()) {
            return true;
        }
        
        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canCreate(User $user)
    {
        if ($user->hasRoleCDPAdmin()) {
            return true;
        }

        if ($user->isInscritCommunautePratique()) {
            return true;
        }

        return false;
    }

    /**
     * @param User|null $user
     * @param Discussion $discussion
     *
     * @return bool
     */
    public function canAccess(User $user = null, Discussion $discussion)
    {
        if ($discussion->isPublic()) {
            return true;
        }

        if (null === $user) {
            return false;
        }

        if ($this->canManage($user)) {
            return true;
        }

        foreach ($discussion->getGroups() as $group) {
            if ($user->isRegisteredInCDPGroup($group)) {
                return true;
            }
        }

        return false;
    }
}
