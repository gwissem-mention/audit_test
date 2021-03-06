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
    const VALIDATE = 'validate';
    const VIEW_FILE = 'view_file';
    const MOVE_MESSAGE = 'move_message';

    /**
     * @param string $attribute
     * @param Message $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW_FILE, self::VALIDATE, self::MARK_AS_HELPFUL, self::DELETE, self::EDIT, self::MOVE_MESSAGE])) {
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

        if ($attribute === self::VIEW_FILE) {
            return $this->canRead($subject, $user instanceof User ? $user : null);
        }

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::MARK_AS_HELPFUL:
            case self::VALIDATE:
            case self::MOVE_MESSAGE:
                return $this->canManage($subject, $user);
            case self::EDIT:
            case self::DELETE:
                return $this->canEdit($subject, $user);
        }

        return false;
    }

    /**
     * @param Message $message
     * @param User|null $user
     *
     * @return bool
     */
    protected function canRead(Message $message, User $user = null)
    {
        if ($message->getUser() === $user) {
            return true;
        }

        if ($message->getDiscussion()->isPublic()) {
            return true;
        }

        foreach ($message->getDiscussion()->getGroups() as $group) {
            if ($group->getUsers()->contains($user)) {
                return true;
            }
        }

        if ($user->hasRoleCDPAdmin()) {
            return true;
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
        if ($user->hasRoleCDPAdmin() || $user->getCommunautePratiqueAnimateurGroupes()->count()) {
            return true;
        }

        if ($message->getUser()->getId() === $user->getId()) {
            return true;
        }

        return false;
    }

    /**
     * @param Message $message
     * @param User $user
     *
     * @return bool
     */
    protected function canManage(Message $message, User $user)
    {
        if ($user->hasRoleCDPAdmin() || $user->getCommunautePratiqueAnimateurGroupes()->count()) {
            return true;
        }

        return false;
    }
}
