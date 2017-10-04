<?php

namespace HopitalNumerique\ObjetBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CartBundle\Model\Report\Infradoc;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Repository\ConsultationRepository;
use HopitalNumerique\ObjetBundle\Repository\SubscriptionRepository;
use Html2Text\Html2Text;
use Nodevo\MailBundle\Service\Traits\MailManagerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PublicationNotificationProviderAbstract.
 */
abstract class PublicationNotificationProviderAbstract extends NotificationProviderAbstract
{
    use MailManagerAwareTrait;

    const SECTION_CODE = 'publication';

    /**
     * @var ConsultationRepository $consultationRepository
     */
    protected $consultationRepository;

    /**
     * @var SubscriptionRepository $subscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * PublicationNotificationProviderAbstract constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     * @param ConsultationRepository $consultationRepository
     * @param SubscriptionRepository $subscriptionRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        ConsultationRepository $consultationRepository,
        SubscriptionRepository $subscriptionRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage, $translator);
        $this->consultationRepository = $consultationRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->templatePath = '@HopitalNumeriqueObjet/notifications/' . $this::NOTIFICATION_CODE . '.html.twig';
    }

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
    }

    /**
     * Returns users concerned by notification, in this case users who have subscribed to the object / the infradoc.
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->subscriptionRepository->getSubscribersQueryBuilder(
            $notification->getCreatedAt(),
            $notification->getData('idPublication'),
            $notification->getData('idInfradoc')
        );
    }

    /**
     * Removes HTML from text and limit length.
     *
     * @param $htmlText string   Text to be processed.
     * @param $limit    int|bool Max text length (see NotificationProvider static method getLimitNotify...)
     *
     * @return string
     */
    protected function processText($htmlText, $limit = false)
    {
        //Remove HTML code
        $htmlToPdf = new Html2Text($htmlText, ['do_links' => 'none', 'width' => 0]);
        $cleanText = $htmlToPdf->getText();

        //Truncate and return
        return $limit ? mb_strimwidth($cleanText, 0, $limit, '...') : $cleanText;
    }

    public function generateOptions(Objet $object, Contenu $infradoc = null)
    {
        return [
            'idPublication'    => $object->getId(),
            'idInfradoc'       => $infradoc ? $infradoc->getId() : null,
            'titrePublication' => $infradoc ? $infradoc->getTitre() : $object->getTitre(),
        ];
    }
}
