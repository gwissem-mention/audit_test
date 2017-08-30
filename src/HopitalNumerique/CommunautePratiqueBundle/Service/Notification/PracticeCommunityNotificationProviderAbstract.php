<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\PublicationBundle\Twig\PublicationExtension;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Html2Text\Html2Text;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class PracticeCommunityNotificationProviderAbstract.
 */
abstract class PracticeCommunityNotificationProviderAbstract extends NotificationProviderAbstract
{
    const SECTION_CODE = 'practice_community';

    /**
     * @var PublicationExtension $publicationExtension
     */
    protected $publicationExtension;

    /**
     * PracticeCommunityNotificationProviderAbstract constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param PublicationExtension $publicationExtension
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        PublicationExtension $publicationExtension
    ) {
        $this->publicationExtension = $publicationExtension;
        parent::__construct($eventDispatcher, $tokenStorage);
    }

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
    }

    /**
     * @param $comment
     * @param $limit
     *
     * @return string
     */
    public function processComment($comment, $limit)
    {
        //Parse custom publication tags
        $htmlText = $this->publicationExtension->parsePublication($comment);

        //Remove HTML code
        $htmlToPdf = new Html2Text($htmlText, ['do_links' => 'none', 'width' => 0]);
        $cleanText = $htmlToPdf->getText();

        //Truncate and return
        return mb_strimwidth($cleanText, 0, $limit, '...');
    }
}
