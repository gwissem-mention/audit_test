<?php
namespace HopitalNumerique\AutodiagBundle\Service;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\UserBundle\Entity\User;

class Share
{
    /** @var EntityManager */
    protected $manager;

    /**
     * Share constructor.
     * @param $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Share synthesis from a comma separated emails
     *
     * @param Synthesis $synthesis
     * @param $string
     * @return Synthesis
     */
    public function shareFromString(Synthesis $synthesis, $string)
    {
        $userRepository = $this->manager->getRepository(User::class);
        $notFounds = [];

        $emails = explode(',', $string);
        foreach ($emails as $email) {
            $email = trim($email);
            $user = $userRepository->findOneByEmail($email);
            if ($user) {
                $synthesis->addShare($user);
            } else {
                $notFounds[] = $email;
            }
        }

        return $notFounds;
    }
}
