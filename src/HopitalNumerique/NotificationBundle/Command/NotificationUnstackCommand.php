<?php

namespace HopitalNumerique\NotificationBundle\Command;

use HopitalNumerique\NotificationBundle\Domain\Command\SendNotificationCommand;
use HopitalNumerique\NotificationBundle\Domain\Command\SendNotificationHandler;
use HopitalNumerique\NotificationBundle\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class NotificationUnstackCommand.
 */
class NotificationUnstackCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('notification-unstack')
            ->setDescription('Notification unstak process, reads, checks, sends and then deletes notifications.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get(SendNotificationHandler::class)->handle(
            new SendNotificationCommand(
                $this->getContainer()->get(NotificationRepository::class)
                    ->getNotificationsToSend(new \DateTime())
            )
        );
    }
}
