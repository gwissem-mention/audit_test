<?php

namespace HopitalNumerique\ForumBundle\Service\Notification;

use CCDNComponent\BBCodeBundle\Component\TwigExtension\BBCodeExtension;
use HopitalNumerique\ForumBundle\Entity\Post;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\ForumBundle\Repository\SubscriptionRepository;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Html2Text\Html2Text;
use Nodevo\MailBundle\Service\Traits\MailManagerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ForumNotificationProviderAbstract.
 */
abstract class ForumNotificationProviderAbstract extends NotificationProviderAbstract
{
    use MailManagerAwareTrait;

    const SECTION_CODE = 'forum';

    /**
     * @var BBCodeExtension $bbCodeExtension
     */
    protected $bbCodeExtension;

    /**
     * @var SubscriptionRepository $subscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * ForumNotificationProviderAbstract constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     * @param BBCodeExtension $bbCodeExtension
     * @param SubscriptionRepository $subscriptionRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        BBCodeExtension $bbCodeExtension,
        SubscriptionRepository $subscriptionRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage, $translator);
        $this->bbCodeExtension = $bbCodeExtension;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->templatePath = '@HopitalNumeriqueForum/notifications/' . $this::getNotificationCode() . '.html.twig';
    }

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
    }

    /**
     * @return integer
     */
    public static function getSectionPosition()
    {
        return 3;
    }

    /**
     * Removes HTML from text and limit length.
     *
     * @param $bbText string   Text to be processed.
     * @param $limit  int|bool Max text length (see NotificationProvider static method getLimitNotify...)
     *
     * @return string
     */
    protected function processText($bbText, $limit = false)
    {
        //Parse BB Code
        $htmlText = $this->bbCodeExtension->BBCodeParse($bbText);

        //Remove HTML code
        $htmlToPdf = new Html2Text($htmlText, ['do_links' => 'none', 'width' => 0]);
        $cleanText = $htmlToPdf->getText();

        //Truncate and return
        return $limit ? mb_strimwidth($cleanText, 0, $limit, '...') : $cleanText;
    }

    /**
     * Commons options of all providers
     *
     * @param Topic $topic
     * @param Post $post
     *
     * @return array
     */
    public function generateOptions(Topic $topic, Post $post)
    {
        return [
            'id' => $topic->getBoard()->getId(),
            'topicId' => $topic->getId(),
            'pseudoAuteur' => null !== $post->getCreatedBy()->getPseudonym()
                ? $post->getCreatedBy()->getPseudonym()
                : $post->getCreatedBy()->getPrenomNom()
            ,
            'forum' => $topic->getBoard()->getCategory()->getForum()->getName(),
            'categorie' => $topic->getBoard()->getCategory()->getName(),
            'theme' => $topic->getBoard()->getName(),
            'message' => $post->getBody(),
            'fildiscussion' => $topic->getTitle()
        ];
    }
}
