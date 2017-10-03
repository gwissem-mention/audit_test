<?php

namespace HopitalNumerique\FichierBundle\Security\File;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\FichierBundle\Entity\File;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class FileVoter
 */
class FileVoter extends Voter
{
    const VIEW = 'view';
    const DELETE = 'delete';

    /**
     * @param string $attribute
     * @param File $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::DELETE])) {
            return false;
        }

        if (!$subject instanceof File) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param File $subject
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
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::VIEW:
                return $this->canView($subject, $user);
        }

        return false;
    }

    /**
     * @param File $file
     * @param User $user
     *
     * @return bool
     */
    protected function canDelete(File $file, User $user)
    {
        if (!$this->canView($file, $user)) {
            return false;
        }

        if ($file->isActive() === false) {
            return true;
        }

        return false;
    }

    /**
     * @param File $file
     * @param User $user
     *
     * @return bool
     */
    protected function canView(File $file, User $user)
    {
        if ($file->getOwner() === $user) {
            return true;
        }

        if ($user->hasRoleAdmin()) {
            return true;
        }

        return false;
    }
}
