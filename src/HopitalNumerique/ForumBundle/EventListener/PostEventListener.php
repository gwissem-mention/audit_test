<?php
namespace HopitalNumerique\ForumBundle\EventListener;

use CCDNForum\ForumBundle\Component\Dispatcher\ForumEvents;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserPostEvent;
use CCDNForum\ForumBundle\Model\FrontModel\ModelInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Nodevo\MailBundle\Manager\MailManager;

use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Listener pour check le post avant un save
 */
class PostEventListener implements EventSubscriberInterface
{
    private $_postModel;
    private $_mailManager;
    private $_userManager;
    private $_mailer;

    public function __construct(ModelInterface $postModel,MailManager $mailManager, UserManager $userManager, $mailer)
    {
        $this->_postModel   = $postModel;
        $this->_mailManager = $mailManager;
        $this->_userManager = $userManager;
        $this->_mailer      = $mailer;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'hopitalnumerique.user.post.create.success' => 'moderationPost',
            ForumEvents::USER_POST_EDIT_SUCCESS   => 'moderationPost',
        );
    }

    public function moderationPost(UserPostEvent $event)
    {
        $post = $event->getPost();

        //Récupération des urls complètes
        // The Regular Expression filter
        $reg_exUrl = "/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/";

        // Check if there is a url in the text
        preg_match_all($reg_exUrl, $post->getBody(), $matchesURLTemp);
        if(count($matchesURLTemp[0]) > 0 )
        {
            //Desactive le post
            $post->setEnAttente(true);

            //Sauvegarde du post modifié
            $this->_postModel->savePost($post);

            //Envoie de mail au mail de contact renseigné dans le domaine
            $options = array(
                'forum'             => $post->getTopic()->getBoard()->getCategory()->getForum()->getName(),
                'categorie'         => $post->getTopic()->getBoard()->getCategory()->getName(),
                'theme'             => $post->getTopic()->getBoard()->getName(),
                'fildiscusssion'    => $post->getTopic()->getTitle(),
                'lienversmessage'   => 'lien'
            );

            $mail = $this->_mailManager->sendNouveauMessageForumAttenteModerationMail($options, $post->getTopic()->getId());
            $this->_mailer->send($mail);
        }
    }
}