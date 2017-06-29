<?php

namespace HopitalNumerique\UserBundle\Command;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class JobLabelMigratoryCommand
 */
class JobLabelMigratoryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:migrate:joblabel')
            ->setDescription('Sets usr_fonction_strucutre value into usr_fonction_dans_etablissement')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $users = $this->getContainer()->get('hopitalnumerique_user.repository.user')->findAll();

        $i = 0;
        $memory = [];

        /** @var User $user */
        foreach ($users as $user) {
            if (null === $user->getJobLabel() || "" === $user->getJobLabel()) {
                $user->setJobLabel($user->getFonctionStructure());
            }

            $user->setFonctionStructure(null);

            $memory[] = $user;

            if ($i % 100 === 0) {
                $em->flush();

                array_walk($memory, function ($user) use ($em) {
                    $em->detach($user);
                });
                $memory = [];
            }

            $i++;
        }

        $em->flush();

        return true;
    }
}
