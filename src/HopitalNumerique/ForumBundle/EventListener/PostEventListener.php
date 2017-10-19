<?php

namespace HopitalNumerique\ForumBundle\EventListener;

use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent;
use CCDNForum\ForumBundle\Component\Dispatcher\ForumEvents;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserPostEvent;
use CCDNForum\ForumBundle\Entity\Model\Post;
use CCDNForum\ForumBundle\Model\FrontModel\ModelInterface;
use HopitalNumerique\ForumBundle\Event\PostEvent;
use HopitalNumerique\ForumBundle\Events;
use HopitalNumerique\ForumBundle\Model\FrontModel\SubscriptionModel;
use HopitalNumerique\ForumBundle\Model\Component\Repository\PostRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Nodevo\MailBundle\Manager\MailManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Checks the contents of the post before saving it
 */
class PostEventListener implements EventSubscriberInterface
{
    /**
     * @var ModelInterface
     */
    private $postModel;

    /**
     * @var MailManager
     */
    private $mailManager;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var SubscriptionModel
     */
    private $subscriptionModel;

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * PostEventListener constructor.
     *
     * @param ModelInterface $postModel
     * @param MailManager $mailManager
     * @param UserManager $userManager
     * @param $mailer
     * @param TokenStorage $tokenStorage
     * @param SubscriptionModel $subscriptionModel
     * @param PostRepository $postRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ModelInterface $postModel,
        MailManager $mailManager,
        UserManager $userManager,
        $mailer,
        TokenStorage $tokenStorage,
        SubscriptionModel $subscriptionModel,
        PostRepository $postRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->postModel = $postModel;
        $this->mailManager = $mailManager;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->tokenStorage = $tokenStorage;
        $this->subscriptionModel = $subscriptionModel;
        $this->postRepository = $postRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::POST_CREATED => 'moderatePost',
            ForumEvents::USER_POST_EDIT_SUCCESS => 'moderatePost',
            ForumEvents::USER_TOPIC_CREATE_COMPLETE => 'moderateTopic',
        ];
    }

    /**
     * @param UserPostEvent $event
     */
    public function moderatePost(UserPostEvent $event)
    {
        $this->moderate($event->getPost());
    }

    /**
     * @param UserTopicEvent $event
     */
    public function moderateTopic(UserTopicEvent $event)
    {
        $this->moderate($event->getTopic()->getFirstPost());
    }

    /**
     * Disables the post if it contains links and sends an e-mail to the domain manager
     *
     * @param Post $post
     */
    private function moderate(Post $post)
    {
        // The Regular Expression filter
        $reg_exUrl = "/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/";

        // Check if there is a url in the text
        preg_match_all($reg_exUrl, $post->getBody(), $matchesURLTemp);
        if (count($matchesURLTemp[0]) > 0 || strstr($post->getBody(), '[URL') || strstr($post->getBody(), '[LINK')) {
            // Disable the post
            $post->setEnAttente(true);

            // Save the modified post
            $this->postModel->savePost($post);

            $user = $post->getCreatedBy();

            // Sending the alert to the domain's e-mail address
            $options = [
                'forum' => $post->getTopic()->getBoard()->getCategory()->getForum()->getName(),
                'categorie' => $post->getTopic()->getBoard()->getCategory()->getName(),
                'theme' => $post->getTopic()->getBoard()->getName(),
                'fildiscussion' => $post->getTopic()->getTitle(),
                'lienversmessage' => 'lien',
                'pseudouser' => !is_null($user->getPseudonym()) ? $user->getPseudonym() : $user->getNomPrenom(),
                'shortMessage' => $post->getBody(),
            ];

            $mail = $this->mailManager->sendNouveauMessageForumAttenteModerationMail(
                $options,
                $post->getTopic()->getId()
            );

            $this->mailer->send($mail);
        } else {
            // Disable the post
            $post->setEnAttente(false);

            // Save the modified post
            $this->postModel->savePost($post);

            $this->eventDispatcher->dispatch(Events::POST_PUBLISHED, new PostEvent($post));
        }
    }
}
