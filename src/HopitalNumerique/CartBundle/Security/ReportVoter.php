<?php

namespace HopitalNumerique\CartBundle\Security;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Entity\ReportSharing;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReportVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Report) {
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

        if (!$user instanceof User) {
            return false;
        }

        /** @var Report $report */
        $report = $subject;

        switch ($attribute) {
            case self::EDIT:
            case self::VIEW:
                return $this->hasAccess($report, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param Report $report
     * @param User $user
     *
     * @return bool
     */
    private function hasAccess(Report $report, User $user)
    {
        if ($report->getOwner() === $user) {
            return true;
        }

        foreach ($report->getShares() as $share) {
            if ($share->getType() === ReportSharing::TYPE_SHARE && $share->getTarget() === $user) {
                return true;
            }
        }

        return false;
    }
}
