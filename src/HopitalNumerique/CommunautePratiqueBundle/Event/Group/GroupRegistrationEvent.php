<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event\Group;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
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
     * @var array
     */
    protected $domains;

    /**
     * GroupRegistrationEvent constructor.
     *
     * @param User $user
     * @param Groupe $group
     * @param Reponse[] $answers
     * @param Domaine[]|null $domains
     */
    public function __construct(User $user, Groupe $group, array $answers, array $domains = null)
    {
        $this->user = $user;
        $this->group = $group;
        $this->answers = $answers;
        $this->domains = $domains;
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

    /**
     * @return array
     */
    public function getDomains()
    {
        return $this->domains;
    }
}
