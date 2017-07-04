<?php

namespace HopitalNumerique\RechercheParcoursBundle\Security;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GuidedSearchVoter extends Voter
{
    const ACCESS = 'access';

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (
            !in_array($attribute, array(self::ACCESS)) ||
            !$subject instanceof GuidedSearch
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        /** @var GuidedSearch $guidedSearch */
        $guidedSearch = $subject;

        if (is_null($guidedSearch->getOwner())) {
            return true;
        }

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::ACCESS:
                return $this->canAccess($guidedSearch, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param GuidedSearch $guidedSearch
     * @param User $user
     *
     * @return bool
     */
    private function canAccess(GuidedSearch $guidedSearch, User $user)
    {
        return $guidedSearch->getOwner() === $user || $guidedSearch->getShares()->contains($user);
    }

}
