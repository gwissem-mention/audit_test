<?php

namespace HopitalNumerique\NewAccountBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use \HopitalNumerique\UserBundle\Entity\User;

/**
 * Class InformationsAccessVoter
 */
class InformationsAccessVoter extends Voter
{
    const ACCESS_PAYMENTS_ACTIVITY = 'access_payments_activity';
    const ACCESS_CONTRACTS = 'access_contracts';
    const IS_EXPERT = 'is_expert';
    const IS_ANAP_ADMIN = 'is_anap_admin';

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [
            self::ACCESS_PAYMENTS_ACTIVITY,
            self::ACCESS_CONTRACTS,
            self::IS_EXPERT,
            self::IS_ANAP_ADMIN
        ]);
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
            case self::ACCESS_PAYMENTS_ACTIVITY:
                return $this->canAccessPaymentsActivity($user);
            case self::ACCESS_CONTRACTS:
                return $this->canAccessContracts($user);
            case self::IS_EXPERT:
                return $this->isExpert($user);
            case self::IS_ANAP_ADMIN:
                return $this->isAnapOrAdmin($user);
        }

        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canAccessPaymentsActivity(User $user)
    {
        return
            $user->hasRoleAmbassadeur() ||
            $user->hasRoleExpert() ||
            $user->hasRoleAdmin() ||
            $user->hasRoleAdminHn()
        ;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canAccessContracts(User $user)
    {
        return
            $user->hasRoleAdminHn() ||
            $user->hasRoleAdmin() ||
            $user->hasRoleExpert() ||
            $user->hasRoleAmbassadeur()
        ;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isExpert(User $user)
    {
        return $user->hasRoleExpert();
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isAnapOrAdmin(User $user) {
        return $user->hasRoleAdmin() || $user->hasRoleAnap();
    }
}
