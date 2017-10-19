<?php

namespace HopitalNumerique\ForumBundle\Domain\Command;

use HopitalNumerique\ForumBundle\Entity\Subscription;
use HopitalNumerique\ForumBundle\Model\FrontModel\SubscriptionModel;
use Nodevo\MailBundle\Manager\MailManager;

/**
 * Class SendEmailToSubscriberHandler
 */
class SendEmailToSubscriberHandler
{
    /**
     * @var SubscriptionModel
     */
    private $subscriptionModel;

    /**
     * @var MailManager
     */
    private $mailManager;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * SendEmailToSubscriberHandler constructor.
     *
     * @param SubscriptionModel $subscriptionModel
     * @param MailManager       $mailManager
     * @param \Swift_Mailer     $mailer
     */
    public function __construct(SubscriptionModel $subscriptionModel, MailManager $mailManager, \Swift_Mailer $mailer)
    {
        $this->subscriptionModel = $subscriptionModel;
        $this->mailManager = $mailManager;
        $this->mailer = $mailer;
    }

    /**
     * @param SendEmailToSubscriberCommand $command
     */
    public function handle(SendEmailToSubscriberCommand $command)
    {
        $topic = $command->post->getTopic();
        $subscriptions = $this->subscriptionModel->findAllSubscriptionsToSend($topic);

        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            if ($command->post->getCreatedBy() !== $subscription->getOwnedBy()) {
                $options = [
                    'user'            => $subscription->getOwnedBy()->getNomPrenom(),
                    'forum'           => $topic->getBoard()->getCategory()->getForum()->getName(),
                    'categorie'       => $topic->getBoard()->getCategory()->getName(),
                    'theme'           => $topic->getBoard()->getName(),
                    'fildiscussion'  => $topic->getTitle(),
                    'lienversmessage' => 'lien',
                    'pseudouser'      => !is_null($command->user->getPseudonym())
                        ? $command->user->getPseudonym() : $command->user->getNomPrenom(),
                    'shortMessage'    => $command->post->getBody(),
                    'id' => $topic->getId()
                ];

                $this->mailManager->sendForumPostCreatedNotification(
                    $subscription->getOwnedBy(),
                    $options
                );
            }
        }

        $this->subscriptionModel->markTheseAsUnread($subscriptions, $command->user);
    }
}
