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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class PublicationNotificationProviderAbstract.
 */
abstract class PublicationNotificationProviderAbstract extends NotificationProviderAbstract
{
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
     * @param ConsultationRepository $consultationRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        ConsultationRepository $consultationRepository,
        SubscriptionRepository $subscriptionRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage);
        $this->consultationRepository = $consultationRepository;
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

    public function generateOptions(Objet $object, Contenu $content)
    {
        return [
            [
                'idPublication'    => $object->getId(),
                'idInfradoc'       => $content ? $content->getId() : null,
                'titrePublication' => $content ? $content->getTitre() : $object->getTitre(),
            ]
        ];
    }
}
