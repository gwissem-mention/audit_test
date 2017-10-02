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
    const CREATE = 'cdp_discussion_create';
    const REPLY = 'cdp_discussion_reply';
    const MARK_AS_RECOMMENDED = 'mark_as_recommended';
    const COPY_TO_GROUP = 'copy_to_group';
    const DOWNLOAD = 'download';
    const MANAGE_DOMAINS = 'manage_domains';
    const REORDER = 'reorder_discussion';

    /**
     * @param string $attribute
     * @param Discussion $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::REORDER, self::DOWNLOAD, self::MARK_AS_RECOMMENDED, self::COPY_TO_GROUP, self::CREATE, self::REPLY])) {
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

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::REORDER:
            case self::MARK_AS_RECOMMENDED:
            case self::COPY_TO_GROUP:
            case self::MANAGE_DOMAINS:
                return $this->canManage($user);
            case self::CREATE:
            case self::REPLY:
            case self::DOWNLOAD:
                return $this->canCreate($user);
        }

        return false;
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
}
