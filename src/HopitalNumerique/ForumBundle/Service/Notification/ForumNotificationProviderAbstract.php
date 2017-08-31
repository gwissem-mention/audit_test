<?php

namespace HopitalNumerique\ForumBundle\Service\Notification;

use CCDNComponent\BBCodeBundle\Component\TwigExtension\BBCodeExtension;
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
     * ForumNotificationProviderAbstract constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param BBCodeExtension $bbCodeExtension
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        BBCodeExtension $bbCodeExtension
    ) {
        $this->bbCodeExtension = $bbCodeExtension;
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
     * Removes HTML from text and limit length.
     *
     * @param $bbText string Text to be processed.
     * @param $limit  int    Max text length (see NotificationBundle constants)
     *
     * @return string
     */
    protected function processText($bbText, $limit)
    {
        //Parse BB Code
        $htmlText = $this->bbCodeExtension->BBCodeParse($bbText);

        //Remove HTML code
        $htmlToPdf = new Html2Text($htmlText, ['do_links' => 'none', 'width' => 0]);
        $cleanText = $htmlToPdf->getText();

        //Truncate and return
        return mb_strimwidth($cleanText, 0, $limit, '...');
    }
}
