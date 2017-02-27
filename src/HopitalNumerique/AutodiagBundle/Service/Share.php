<?php

namespace HopitalNumerique\AutodiagBundle\Service;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Event\SynthesisEvent;
use HopitalNumerique\AutodiagBundle\Events;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Share
{
    /** @var EntityManager */
    protected $manager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Share constructor.
     *
     * @param $manager
     */
    public function __construct(EntityManager $manager, EventDispatcherInterface $eventDispatcher)
    {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Share synthesis from a comma separated emails.
     *
     * @param Synthesis $synthesis
     * @param $string
     *
     * @return array
     */
    public function shareFromString(Synthesis $synthesis, $string)
    {
        $userRepository = $this->manager->getRepository(User::class);
        $notFounds = [];
        $emails = explode(',', $string);
        foreach ($emails as $email) {
            $email = trim($email);
            $user = $userRepository->findOneByEmail($email);
            if ($user && $user !== $synthesis->getUser()) {
                $synthesis->addShare($user);
            } else {
                $notFounds[] = $email;
            }
        }

        $event = new SynthesisEvent($synthesis);
        $this->eventDispatcher->dispatch(Events::SYNTHESIS_SHARED, $event);

        return $notFounds;
    }
}
