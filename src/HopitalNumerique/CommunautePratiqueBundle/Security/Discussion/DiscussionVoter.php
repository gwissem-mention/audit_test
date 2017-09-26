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

    /**
     * @param string $attribute
     * @param Discussion $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::CREATE, self::REPLY])) {
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
            case self::MARK_AS_RECOMMENDED:
                return $this->markAsRecommended($user, $subject);
            case self::CREATE:
            case self::REPLY:
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
    public function markAsRecommended(User $user, Discussion $discussion)
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
