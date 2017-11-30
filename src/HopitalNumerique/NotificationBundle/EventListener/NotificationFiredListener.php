<?php

namespace HopitalNumerique\NotificationBundle\EventListener;

use HopitalNumerique\NotificationBundle\Domain\Command\ProcessNotificationCommand;
use HopitalNumerique\NotificationBundle\Domain\Command\ProcessNotificationHandler;
use HopitalNumerique\NotificationBundle\Event\NotificationEvent;
use HopitalNumerique\NotificationBundle\Events;
use HopitalNumerique\NotificationBundle\Exception\MissingProviderException;
use HopitalNumerique\NotificationBundle\Exception\ProviderNotFoundException;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class NotificationFiredListener.
 */
class NotificationFiredListener implements EventSubscriberInterface
{
    /**
     * @var Notifications $notificationService
     */
    protected $notificationService;

    /**
     * @var TokenStorage $tokenStorage
     */
    protected $tokenStorage;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var ProcessNotificationHandler $processNotificationHandler
     */
    protected $processNotificationHandler;

    /**
     * NotificationFiredListener constructor.
     *
     * @param Notifications              $notificationService
     * @param TokenStorage               $tokenStorage
     * @param LoggerInterface            $logger
     * @param ProcessNotificationHandler $processNotificationHandler
     */
    public function __construct(
        Notifications $notificationService,
        TokenStorage $tokenStorage,
        LoggerInterface $logger,
        ProcessNotificationHandler $processNotificationHandler
    ) {
        $this->notificationService = $notificationService;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        $this->processNotificationHandler = $processNotificationHandler;
    }

    /**
     * @param NotificationEvent $event
     *
     * @throws MissingProviderException
     * @throws ProviderNotFoundException
     */
    public function onFireNotification(NotificationEvent $event)
    {
        $providerCode = $event->getNotification()->getNotificationCode();

        if (!$providerCode) {
            throw new MissingProviderException('Notification provider not supplied');
        }

        $provider = $this->notificationService->getProvider($providerCode);

        if (!$provider) {
            throw new ProviderNotFoundException(sprintf('Notification provider not found (code : %s)', $providerCode));
        }

        $command = new ProcessNotificationCommand($event->getNotification());
        $this->processNotificationHandler->handle($command);
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::FIRE_NOTIFICATION => 'onFireNotification',
        ];
    }
}
