<?php

namespace HopitalNumerique\NotificationBundle\Controller;

use HopitalNumerique\NotificationBundle\Domain\Command\SendNotificationCommand;
use HopitalNumerique\NotificationBundle\Domain\Command\SendNotificationHandler;
use HopitalNumerique\NotificationBundle\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class NotificationController.
 */
class NotificationController extends Controller
{
    public function testCommandAction()
    {
        $this->get(SendNotificationHandler::class)->handle(
            new SendNotificationCommand(
                $this->get(NotificationRepository::class)
                    ->getNotificationsToSend(new \DateTime())
            )
        );
    }
}
