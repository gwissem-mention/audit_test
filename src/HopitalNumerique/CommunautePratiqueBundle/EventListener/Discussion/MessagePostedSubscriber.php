<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener\Discussion;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Read;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\MessagePostedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\ReadRepository;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\SubscriptionRepository;
use HopitalNumerique\CoreBundle\Service\ObjectIdentity\UserSubscription;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use Nodevo\MailBundle\Manager\MailManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MessagePostedSubscriber implements EventSubscriberInterface
{
    /**
     * @var ReadRepository $readRepository
     */
    protected $readRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var AuthorizationCheckerInterface $authorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var MailManager
     */
    protected $mailManager;

    /**
     * @var UserSubscription
     */
    protected $userSubscription;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * MessagePostedSubscriber constructor.
     *
     * @param ReadRepository $readRepository
     * @param EntityManagerInterface $entityManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param SubscriptionRepository $subscriptionRepository
     * @param MailManager $mailManager
     * @param UserSubscription $userSubscription
     * @param UserRepository $userRepository
     */
    public function __construct(
        ReadRepository $readRepository,
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker,
        SubscriptionRepository $subscriptionRepository,
        MailManager $mailManager,
        UserSubscription $userSubscription,
        UserRepository $userRepository
    ) {
        $this->readRepository = $readRepository;
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->mailManager = $mailManager;
        $this->userSubscription = $userSubscription;
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::DISCUSSION_MESSAGE_POSTED => [
                ['moderateMessage', 0],
                ['readDiscussion', 0],
                ['autoSubscribe', 0],
            ],
            Events::DISCUSSION_MESSAGE_CREATED => [
                ['autoSubscribe', 0],
                ['notifySubscriber', 0],
            ]
        ];
    }

    /**
     * @param MessagePostedEvent $event
     */
    public function autoSubscribe(MessagePostedEvent $event)
    {
        $this->userSubscription->subscribe($event->getMessage()->getDiscussion(), $event->getMessage()->getUser());

        if ($event->getMessage()->getDiscussion()->getMessages()->count() > 1) {
            return;
        }

        if ($event->getMessage()->getDiscussion()->isPublic()) {
            foreach ($event->getMessage()->getDiscussion()->getDomains() as $domain) {
                foreach ($this->userRepository->getCommunautePratiqueMembresQueryBuilder(null, $domain)->getQuery()->getResult() as $user) {
                    $this->userSubscription->subscribe($event->getMessage()->getDiscussion(), $user);
                }
            }
        } else {
            foreach ($event->getMessage()->getDiscussion()->getGroups() as $group) {
                foreach ($group->getUsers() as $user) {
                    $this->userSubscription->subscribe($event->getMessage()->getDiscussion(), $user);
                }
            }
        }
    }

    /**
     * @param MessagePostedEvent $event
     */
    public function readDiscussion(MessagePostedEvent $event)
    {
        $message = $event->getMessage();
        $author = $message->getUser();
        $discussion = $message->getDiscussion();

        if ($read = $this->readRepository->findOneByUserAndDiscussion($author, $discussion)) {
            $read->setLastMessageDate($message->getCreatedAt());
        } else {
            $read = new Read($author, $discussion, $message->getCreatedAt());
            $this->entityManager->persist($read);
        }

        $this->entityManager->flush($read);
    }

    /**
     * @param MessagePostedEvent $event
     */
    public function moderateMessage(MessagePostedEvent $event)
    {
        $message = $event->getMessage();
        $discussion = $message->getDiscussion();

        if (null === $discussion->getMessages() || $discussion->getMessages()->count() === 1) {
            return;
        }

        if ($this->authorizationChecker->isGranted('validate', $message)) {
            return;
        }

        if ($message->needModeration()) {
            $message->setPublished(false);

            $animators = [];
            foreach ($message->getDiscussion()->getGroups() as $group) {
                foreach ($group->getAnimateurs() as $animator) {
                    $animators[$animator->getEmail()] = $animator;
                }
            }

            foreach ($message->getDiscussion()->getDomains() as $domain) {
                $animators[$domain->getAdresseMailContact()] = (new User())->setEmail($domain->getAdresseMailContact());
            }

            foreach ($animators as $animator) {
                $this->mailManager->sendCDPNeedModerationMail($message, $animator);
            }

        }
    }

    /**
     * @param MessagePostedEvent $event
     */
    public function notifySubscriber(MessagePostedEvent $event)
    {
        $message = $event->getMessage();

        if (!$message->isPublished()) {
            return;
        }

        $subscribers = $this->subscriptionRepository->findSubscribers(ObjectIdentity::createFromDomainObject($message->getDiscussion()));
        $subscribers = array_filter($subscribers, function (User $user) use ($message) {
            return $user->getId() !== $message->getuser()->getId();
        });

        foreach ($subscribers as $subscriber) {
            $this->mailManager->sendCDPSubscriptionMail($message, $subscriber);
        }
    }
}
