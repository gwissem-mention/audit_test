<?php

namespace HopitalNumerique\NotificationBundle\Command;

use HopitalNumerique\NotificationBundle\Entity\Notification;
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
        $notificationRepo = $this->getContainer()->get(
            'hopitalnumerique\notificationbundle\repository\notificationrepository'
        );

        $notificationService = $this->getContainer()->get(
            'hopitalnumerique\notificationbundle\service\notifications'
        );

        $subscriptionFinder = $this->getContainer()->get(
            'hopitalnumerique\notificationbundle\service\notificationsubscribtionfinder'
        );

        //Clean duplicates
        $notificationRepo->cleanDuplicates();

        //Process notifications
        $now = new \DateTime();
        foreach ($notificationRepo->getNotificationsToSend($now) as $notificationArray) {
            /** @var Notification $notification */
            $notification = current($notificationArray);

            //Get notification provider from notification code.
            $provider = $notificationService->getProvider($notification->getNotificationCode());

            //Get subscribers list (this list will be limited to notification user in this case).
            $subscribers = $subscriptionFinder->findSubscriptions($notification);

            //Browse results: if user is no more a subscriber we do not send the notification. More detail :
            //We don't care about notification user settings, notification provider 'getSubscribers' will the only to
            //be considered (for instance user was subscriber of a forum board when the notification was created and
            //this user is no more subscribing by the time we send te notification.
            foreach ($subscribers as $subscriber) {
                if ($notification->getUser()->getId() === $subscriber->getUserId()) {
                    $provider->notify($notification);
                }
            }

            //Delete notification
            $notificationRepo->deleteNotification($notification);
        }
    }
}
