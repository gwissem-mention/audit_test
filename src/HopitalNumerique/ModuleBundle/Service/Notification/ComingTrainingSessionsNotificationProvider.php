<?php

namespace HopitalNumerique\ModuleBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ModuleBundle\Entity\Session;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;
use HopitalNumerique\NotificationBundle\Service\Provider\FrequenciesBlacklistInterface;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use Nodevo\AclBundle\Entity\Acl;
use Nodevo\AclBundle\Entity\Ressource;
use Nodevo\AclBundle\Manager\AclManager;
use Nodevo\AclBundle\Manager\RessourceManager;
use Nodevo\MailBundle\Service\Traits\MailManagerAwareTrait;
use Nodevo\RoleBundle\Entity\Role;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ComingTrainingSessionsNotificationProvider.
 */
class ComingTrainingSessionsNotificationProvider extends NotificationProviderAbstract implements FrequenciesBlacklistInterface
{
    use MailManagerAwareTrait;

    const DEFAULT_FREQUENCY = NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_WEEKLY;

    const NOTIFICATION_CODE = 'coming_training_sessions';

    const SECTION_CODE = 'anap_suggestion';

    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * @var AclManager $aclManager
     */
    protected $aclManager;

    /**
     * @var RessourceManager $resourceManager
     */
    protected $resourceManager;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        UserRepository $userRepository,
        AclManager $aclManager,
        RessourceManager $resourceManager
    ) {
        parent::__construct($eventDispatcher, $tokenStorage, $translator);
        $this->userRepository = $userRepository;
        $this->aclManager = $aclManager;
        $this->resourceManager = $resourceManager;
        $this->templatePath = '@HopitalNumeriqueModule/Notifications/' . $this::getNotificationCode() . '.html.twig';
    }

    /**
     * @return string
     */
    public static function getNotificationCode()
    {
        return self::NOTIFICATION_CODE;
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
        return 8;
    }

    /**
     * @return integer
     */
    public static function getNotifPosition()
    {
        return 1;
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param Session $session
     */
    public function fire(Session $session)
    {
        $now = new \DateTime();

        /**
         * @var Role $sessionRole
         */
        $roles = [];
        foreach ($session->getRestrictionAcces() as $sessionRole) {
            $roles[] = $sessionRole->getRole();
        }

        $domains = $session->getModule()->getDomaines()->map(function (Domaine $domain) {
            return $domain->getId();
        })->toArray();

        $this->processNotification(
            $now->format('YmdHis'),
            $session->getModuleTitre(),
            $session->getDescription(),
            [
                'id' => $session->getModule()->getId(),
                'roles' => $roles,
                'domains' => $domains,
                'formateur' => $session->getFormateur()->getPrenomNom(),
                'dateSession' => $session->getDateSessionString(),
                'description' => $session->getDescription(),
            ]
        );
    }

    /**
     * Returns users concerned by notification, in this case users whose role is authorized for training session.
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->userRepository->createUsersByRolesQueryBuilder(
            $notification->getData('roles'),
            $notification->getData('domains')
        );
    }

    /**=
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        if (1 === $notification->getDetailLevel() || NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_STRAIGHT === $notification->getFrequency()) {
            $options['liste'] = sprintf(
                '%s - %s - %s - %s',
                $notification->getTitle(),
                $notification->getData('dateSession'),
                $notification->getData('formateur'),
                $notification->getDetail()
            );
        } else {
            $options['liste'] = $notification->getTitle();
        }
        $this->mailManager->sendNextSessionsNotification($notification->getUser(), $options);
    }

    /**
     * Gets frequencies blacklist.
     *
     * @return array
     */
    public function getForbiddenFrequencies()
    {
        return [
            NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_STRAIGHT,
            NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_DAILY,
        ];
    }
}
