<?php

namespace HopitalNumerique\UserBundle\Security;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Entity\Contractualisation;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class ContractVoter
 */
class ContractVoter extends Voter
{
    const DOWNLOAD = 'download';

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::DOWNLOAD]) && $subject instanceof Contractualisation;
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

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::DOWNLOAD:
                return $this->canDownload($user, $subject);
        }

        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canDownload(User $user, Contractualisation $contract)
    {
        return
            $user->hasRoleAdminHn() ||
            $user->hasRoleAdmin() ||
            $user->hasRoleExpert() ||
            $user->hasRoleAmbassadeur() ||
            $contract->getUser()->getId() === $user->getId()
        ;
    }
}
