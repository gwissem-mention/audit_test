<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use HopitalNumerique\UserBundle\Entity\User;

class ReadMessageCommand
{
    /**
     * @var User $user
     */
    public $user;

    /**
     * @var integer $messageId
     */
    public $messageId;

    /**
     * ReadMessageCommand constructor.
     *
     * @param User $user
     * @param integer $messageId
     */
    public function __construct(User $user, $messageId)
    {
        $this->user = $user;
        $this->messageId = $messageId;
    }
}
