<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event\Group;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

class GroupRegistrationEvent extends Event
{
    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var Groupe $group
     */
    protected $group;

    /**
     * @var Reponse[] $answers
     */
    protected $answers;

    /**
     * GroupRegistrationEvent constructor.
     *
     * @param User $user
     * @param Groupe $group
     * @param Reponse[] $answers
     */
    public function __construct(User $user, Groupe $group, array $answers)
    {
        $this->user = $user;
        $this->group = $group;
        $this->answers = $answers;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Groupe
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return Reponse[]
     */
    public function getAnswers()
    {
        return $this->answers;
    }
}
