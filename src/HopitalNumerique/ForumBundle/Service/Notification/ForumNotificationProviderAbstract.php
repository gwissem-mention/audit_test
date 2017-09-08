<?php

namespace HopitalNumerique\ForumBundle\Service\Notification;

use CCDNComponent\BBCodeBundle\Component\TwigExtension\BBCodeExtension;
use HopitalNumerique\ForumBundle\Repository\SubscriptionRepository;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Html2Text\Html2Text;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ForumNotificationProviderAbstract.
 */
abstract class ForumNotificationProviderAbstract extends NotificationProviderAbstract
{
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
     * @param TokenStorageInterface    $tokenStorage
     * @param BBCodeExtension          $bbCodeExtension
     * @param SubscriptionRepository   $subscriptionRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        BBCodeExtension $bbCodeExtension,
        SubscriptionRepository $subscriptionRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage);
        $this->bbCodeExtension = $bbCodeExtension;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
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
}
