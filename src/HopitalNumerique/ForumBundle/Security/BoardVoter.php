<?php

namespace HopitalNumerique\ForumBundle\Security;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ForumBundle\Entity\Board;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class BoardVoter
 */
class BoardVoter extends Voter
{
    const READ = 'read';

    /**
     * @var RoleHierarchy $roleHierarchy
     */
    protected $roleHierarchy;

    /**
     * BoardVoter constructor.
     *
     * @param RoleHierarchy $roleHierarchy
     */
    public function __construct(RoleHierarchy $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * @param string $attribute
     * @param Board $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::READ])) {
            return false;
        }

        if (!$subject instanceof Board) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Board $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        switch ($attribute) {
            case self::READ:
                return $this->canRead($subject, $user instanceof User ? $user : null);
        }

        return false;
    }

    /**
     * @param Board $message
     * @param User|null $user
     *
     * @return bool
     */
    protected function canRead(Board $board, User $user = null)
    {
        foreach ([$board, $board->getCategory(), $board->getCategory()->getForum()] as $forumPart) {
            if (!$this->checkRoles($user, $forumPart, 'read')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $user
     * @param $forumPart
     * @param $type
     *
     * @return bool
     */
    private function checkRoles($user, $forumPart, $type)
    {
        $functionName = sprintf('get%sAuthorisedRoles', ucfirst($type));

        if (0 == count($forumPart->{$functionName}())) {
            return true;
        }

        foreach ($forumPart->{$functionName}() as $role) {
            if (!$user instanceof User) {
                if ('ROLE_ANONYME_10' === $role) {
                    return true;
                }
            } elseif (in_array($role, $user->getRoles())) {
                return true;
            }
        }

        return false;
    }
}
