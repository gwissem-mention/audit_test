<?php

namespace HopitalNumerique\UserBundle\Command;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OrganizationMigratorCommand
 */
class OrganizationMigratoryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:migrate:organization')
            ->setDescription('Sets usr_nom_structure value into usr_autre_rattachement_sante')
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

        /** @var User $user */
        foreach ($users as $user) {
            if (null === $user->getOrganizationLabel() || "" === $user->getOrganizationLabel()) {
                $user->setOrganizationLabel($user->getNomStructure());
            }
            $user->setNomStructure(null);
        }

        $em->flush();

        return true;
    }
}
