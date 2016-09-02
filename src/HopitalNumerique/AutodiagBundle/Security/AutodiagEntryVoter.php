<?php

namespace HopitalNumerique\AutodiagBundle\Security;

use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Service\AutodiagEntrySession;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AutodiagEntryVoter extends Voter
{
    const EDIT = 'edit';
    const READ = 'read';

    /**
     * @var AutodiagEntrySession
     */
    protected $session;

    public function __construct(AutodiagEntrySession $session)
    {
        $this->session = $session;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::EDIT))) {
            return false;
        }

        if (!$subject instanceof AutodiagEntry) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param AutodiagEntry $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (null === $subject->getId()) {
            return true;
        }

        $user = $token->getUser();

        if ($this->session->has($subject)) {
            return true;
        }

        if ($user instanceof User && $subject->getUser() === $user) {
            return true;
        }

        return false;
    }
}
