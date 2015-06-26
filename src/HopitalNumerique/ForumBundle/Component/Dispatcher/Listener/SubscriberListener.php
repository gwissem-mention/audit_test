<?php
namespace HopitalNumerique\ForumBundle\Component\Dispatcher\Listener;

use CCDNForum\ForumBundle\Component\Dispatcher\Listener\SubscriberListener as CCDNSubscriberListener;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Nodevo\MailBundle\Manager\MailManager;

class SubscriberListener extends CCDNSubscriberListener
{

    /**
     * @access protected
     * @var \Nodevo\MailBundle\Manager\MailManager $mailManager
     */
    protected $mailManager;

    /**
     * @access protected
     * @var \Swift_Mailer $mailer
     */
    protected $mailer;

    /**
     *
     * @access public
     * @param \CCDNForum\ForumBundle\Model\FrontModel\SubscriptionModel $subscriptionModel
     * @param \Symfony\Component\Security\Core\SecurityContext          $securityContext
     */
    public function __construct($subscriptionModel, SecurityContext $securityContext, MailManager $mailManager, \Swift_Mailer $mailer)
    {
        $this->subscriptionModel = $subscriptionModel;
        $this->securityContext   = $securityContext;
        $this->mailManager       = $mailManager;
        $this->mailer            = $mailer;
    }

    public function onTopicCreateComplete(UserTopicEvent $event)
    {
        parent::onTopicCreateComplete($event);
        $this->onTopicReplyComplete($event);
    }
    
    /**
     *
     * @access public
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent $event
     */
    public function onTopicReplyComplete(UserTopicEvent $event)
    {
        if ($event->getTopic()) 
        {
            if ($event->getTopic()->getId()) 
            {
                $user = $this->securityContext->getToken()->getUser();

                if ($event->authorWantsToSubscribe()) 
                {
                    $this->subscriptionModel->subscribe($event->getTopic(), $user);
                }

                $subscriptions = $this->subscriptionModel->findAllSubscriptionsToSend($event->getTopic());

                //Envoie des mails pour les followers
                foreach ($subscriptions as $subscription) 
                {
                    //Sauf à l'utilisateur qui vient de répondre
                    if($user->getId() !== $subscription->getOwnedBy()->getId())
                    {
                        $topic = $event->getTopic();

                        //envoi du mail de confirmation d'inscription
                        $options = array(
                            'user'              => $subscription->getOwnedBy()->getNomPrenom(),
                            'forum'             => $topic->getBoard()->getCategory()->getForum()->getName(),
                            'categorie'         => $topic->getBoard()->getCategory()->getName(),
                            'theme'             => $topic->getBoard()->getName(),
                            'fildiscusssion'    => $topic->getTitle(),
                            'lienversmessage'   => 'lien',
                            'pseudouser'        => !is_null($user->getPseudonymeForum()) ? $user->getPseudonymeForum() : $user->getNomPrenom()
                        );
                        $mail = $this->mailManager->sendNouveauMessageForumMail($subscription->getOwnedBy(), $options, $topic->getId());
                        $this->mailer->send($mail);
                    }
                }

                $this->subscriptionModel->markTheseAsUnread($subscriptions, $user);
            }
        }
    }
}
