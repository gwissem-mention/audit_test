<?php

namespace HopitalNumerique\ReferenceBundle\Security;

use HopitalNumerique\PublicationBundle\Entity\Suggestion;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReferenceVoter extends Voter
{
    const REFERENCE = 'reference';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::REFERENCE])) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === self::REFERENCE) {
            if ($user->hasRoleAdmin() || $user->hasRoleAdminHn() || $user->hasRoleAdminAutodiag() || $user->hasRoleAdminDomaine()) {
                return true;
            }

            if ($subject instanceof Suggestion) {
                return $subject->getUser() === $user;
            }

            if ($subject instanceof User) {
                return $subject->getId() === $user->getId();
            }

            return false;
        }

        throw new \LogicException('Authorization is not implemented.');
    }
}
