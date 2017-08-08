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
            /** @var User $user */
            $user = $userRepository->findOneByEmail($email);
            if ($user && $user->getId() !== $synthesis->getUser()->getId()) {
                // Checks if the user has at least one domain of the autodiag
                if ($synthesis->getAutodiag()->getDomaines()->count() > 0) {
                    $founded = false;
                    foreach ($synthesis->getAutodiag()->getDomaines() as $domain) {
                        if ($user->hasDomaine($domain)) {
                            $founded = true;
                            break;
                        }
                    }

                    // Adds the first domain of the autodiag if a common domain isn't found.
                    if (!$founded) {
                        $user->addDomaine($synthesis->getAutodiag()->getDomaines()->first());
                    }
                }

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
