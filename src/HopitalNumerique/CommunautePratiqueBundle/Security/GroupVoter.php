<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Security;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class GroupVoter
 */
class GroupVoter extends Voter
{
    const ACCESS = 'access';
    const VALIDATE_REGISTRATION = 'validate_registration';

    /**
     * @param string $attribute
     * @param Groupe $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::ACCESS, self::VALIDATE_REGISTRATION])) {
            return false;
        }

        if (!$subject instanceof Groupe) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Groupe $subject
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
            case self::ACCESS:
                return $this->canAccess($subject, $user);
            case self::VALIDATE_REGISTRATION:
                return $this->isAnimator($subject, $user);
        }

        return false;
    }

    /**
     * @param Groupe $group
     * @param User $user
     *
     * @return bool
     */
    protected function canAccess(Groupe $group, User $user)
    {
        if (
            ($this->isAnimator($group, $user)) ||
            ($group->getRequiredRoles()->count() === 0)
        ) {
            return true;
        }

        foreach ($group->getRequiredRoles() as $role) {
            if ($user->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Groupe $group
     * @param User $user
     *
     * @return bool
     */
    public function isAnimator(Groupe $group, User $user)
    {
        if ($user->hasRoleCDPAdmin()) {
            return true;
        }

        return $group->getAnimateurs()->exists(function ($key, User $animator) use ($user) {
            return $animator->getId() === $user->getId();
        });
    }
}
