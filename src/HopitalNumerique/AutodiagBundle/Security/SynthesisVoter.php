<?php

namespace HopitalNumerique\AutodiagBundle\Security;

use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Service\AutodiagEntrySession;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SynthesisVoter extends Voter
{
    const READ = 'read';
    const VALIDATE = 'validate';
    const SHARE = 'share';
    const DELETE = 'delete';

    /**
     * @var AutodiagEntrySession
     */
    protected $autodiagEntrySession;

    public function __construct(AutodiagEntrySession $autodiagEntrySession)
    {
        $this->autodiagEntrySession = $autodiagEntrySession;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::READ, self::SHARE, self::VALIDATE, self::DELETE))) {
            return false;
        }

        if (!$subject instanceof Synthesis) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Synthesis $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        /** @var Synthesis $synthesis */
        $synthesis = $subject;

        switch ($attribute) {
            case self::SHARE:
                return $this->canShare($synthesis, $user);
                break;
            case self::VALIDATE:
                return $this->canValidate($synthesis, $user);
                break;
            case self::READ:
                return $this->canRead($synthesis, $user);
            case self::DELETE:
                return $this->canRemove($synthesis, $user);
                break;
        }

        return false;
    }

    /**
     * Peut valider si la synthèse ne possède qu'une seule entry qui n'est pas une copie d'une autre entry
     * et si l'entry est en train d'être créée
     *    ou si l'entry appartient à l'utilisateur (qui doit être connecté)
     *
     * @param Synthesis $synthesis
     * @param $user
     * @return bool
     */
    public function canValidate(Synthesis $synthesis, $user)
    {
        /** @var AutodiagEntry $firstEntry */
        $firstEntry = $synthesis->getEntries()->first();

        if (count($synthesis->getEntries()) == 1 && !$firstEntry->isCopy()) {
            if (null === $firstEntry->getId()) {
                return true;
            } elseif ($user instanceof User && $firstEntry->getUser() === $user) {
                return true;
            }
        }

        return false;
    }

    /**
     * Peut partager si l'utilisateur possède la synthèse ou est dans les partages de la synthèse,
     * et si la synthèse est validée
     *
     * @param Synthesis $synthesis
     * @param $user
     * @return bool
     */
    public function canShare(Synthesis $synthesis, $user)
    {
        if ($synthesis->getUser() === $user && $synthesis->getValidatedAt() != null) {
            return true;
        }

        foreach ($synthesis->getShares() as $share) {
            if ($share === $user) {
                return true;
            }
        }

        return false;
    }

    /**
     * Peut accéder en lecture si l'utilisateur possède la synthèse
     * ou si l'utilisateur a l'entry de la synthèse en session
     * ou si l'utilisateur est dans la liste de partages de la synthèse
     *
     * @param Synthesis $synthesis
     * @param $user
     * @return bool
     */
    public function canRead(Synthesis $synthesis, $user)
    {
        if ($synthesis->getUser() === $user
            || $this->autodiagEntrySession->has($synthesis->getEntries()->first())) {
            return true;
        } else {
            foreach ($synthesis->getShares() as $share) {
                if ($share === $user) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canRemove(Synthesis $synthesis, $user)
    {
        if ($user instanceof User) {
            /** @var $user User */
            if ($user->hasRoleAdmin() || $user->hasRoleAdminHn() || $user->hasRoleAdminDomaine() || $user->hasRoleAdminAutodiag()) {
                return true;
            }
        }

        if ($synthesis->getUser() === $user || $this->autodiagEntrySession->has($synthesis->getEntries()->first())) {
            return true;
        }

        return false;
    }
}
