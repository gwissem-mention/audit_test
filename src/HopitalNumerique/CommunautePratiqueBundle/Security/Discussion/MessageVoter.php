<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Security\Discussion;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;

/**
 * Class MessageVoter
 */
class MessageVoter extends Voter
{
    const MARK_AS_HELPFUL = 'helpful';
    const DELETE = 'delete';
    const EDIT = 'edit';

    /**
     * @param string $attribute
     * @param Message $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::MARK_AS_HELPFUL, self::DELETE, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Message) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Message $subject
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
            case self::EDIT:
            case self::DELETE:
                return $this->canEdit($subject, $user);
            case self::MARK_AS_HELPFUL:
                return $this->canMarkAsHelpful($subject, $user);
        }

        return false;
    }

    /**
     * @param Message $message
     * @param User $user
     *
     * @return bool
     */
    public function canMarkAsHelpful(Message $message, User $user)
    {
        if ($user->hasRoleCDPAdmin()) {
            return true;
        }

        foreach ($message->getDiscussion()->getGroups() as $group) {
            if ($group->getAnimateurs()->exists(function ($key, User $animator) use ($user) {
                return $animator->getId() === $user->getId();
            })) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Message $message
     * @param User $user
     *
     * @return bool
     */
    public function canEdit(Message $message, User $user)
    {
        if ($user->hasRoleCDPAdmin()) {
            return true;
        }

        if ($message->getUser()->getId() === $user->getId()) {
            return true;
        }

        return false;
    }
}
