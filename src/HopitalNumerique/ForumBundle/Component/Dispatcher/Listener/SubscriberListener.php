<?php

namespace HopitalNumerique\ForumBundle\Component\Dispatcher\Listener;

use CCDNForum\ForumBundle\Component\Dispatcher\Listener\SubscriberListener as CCDNSubscriberListener;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent;
use CCDNForum\ForumBundle\Model\Component\Repository\PostRepository;
use HopitalNumerique\ForumBundle\Entity\Post;
use Symfony\Component\Security\Core\SecurityContext;
use Nodevo\MailBundle\Manager\MailManager;

class SubscriberListener extends CCDNSubscriberListener
{
    /**
     * @var \Nodevo\MailBundle\Manager\MailManager
     */
    protected $mailManager;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /** @var PostRepository $postRepository */
    protected $postRepository;

    /**
     * @param \CCDNForum\ForumBundle\Model\FrontModel\SubscriptionModel $subscriptionModel
     * @param \Symfony\Component\Security\Core\SecurityContext          $securityContext
     */
    public function __construct($subscriptionModel, SecurityContext $securityContext, MailManager $mailManager, \Swift_Mailer $mailer, PostRepository $postRepository)
    {
        $this->subscriptionModel = $subscriptionModel;
        $this->securityContext = $securityContext;
        $this->mailManager = $mailManager;
        $this->mailer = $mailer;
        $this->postRepository = $postRepository;
    }

    public function onTopicCreateComplete(UserTopicEvent $event)
    {
        parent::onTopicCreateComplete($event);
        $this->onTopicReplyComplete($event);
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent $event
     */
    public function onTopicReplyComplete(UserTopicEvent $event)
    {
        if ($event->getTopic()) {
            if ($event->getTopic()->getId()) {
                $user = $this->securityContext->getToken()->getUser();
                $topic = $event->getTopic();

                if ($event->authorWantsToSubscribe()) {
                    $this->subscriptionModel->subscribe($event->getTopic(), $user);
                }

                $subscriptions = $this->subscriptionModel->findAllSubscriptionsToSend($event->getTopic());

                /** @var Post $post */
                $post = $this->postRepository->getLastPostForTopicById($topic->getId());

                //Envoie des mails pour les followers
                foreach ($subscriptions as $subscription) {
                    //Sauf à l'utilisateur qui vient de répondre
                    if ($user->getId() !== $subscription->getOwnedBy()->getId() && !is_null($post)) {
                        $topic = $event->getTopic();

                        //envoi du mail de confirmation d'inscription
                        $options = [
                            'user' => $subscription->getOwnedBy()->getNomPrenom(),
                            'forum' => $topic->getBoard()->getCategory()->getForum()->getName(),
                            'categorie' => $topic->getBoard()->getCategory()->getName(),
                            'theme' => $topic->getBoard()->getName(),
                            'fildiscusssion' => $topic->getTitle(),
                            'lienversmessage' => 'lien',
                            'pseudouser' => !is_null($user->getPseudonymeForum()) ? $user->getPseudonymeForum() : $user->getNomPrenom(),
                            'shortMessage' => $post->getBody(),
                        ];

                        if (false === $post->getEnAttente()) {
                            $mail = $this->mailManager->sendNouveauMessageForumMail($subscription->getOwnedBy(), $options, $topic->getId());
                            $this->mailer->send($mail);
                        }
                    }
                }

                $this->subscriptionModel->markTheseAsUnread($subscriptions, $user);
            }
        }
    }
}
