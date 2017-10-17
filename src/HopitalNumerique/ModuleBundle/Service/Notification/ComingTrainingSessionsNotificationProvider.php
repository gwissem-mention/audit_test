<?php

namespace HopitalNumerique\ModuleBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\ModuleBundle\Entity\Session;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;
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
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ComingTrainingSessionsNotificationProvider.
 */
class ComingTrainingSessionsNotificationProvider extends NotificationProviderAbstract
{
    use MailManagerAwareTrait;

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

        $moduleTitle = $session->getModuleTitre();
        if (strlen($moduleTitle) > self::getLimitNotifyTitleLength()) {
            $moduleTitle = substr($moduleTitle, 0, self::getLimitNotifyTitleLength()) . '...';
        }

        $sessionTitle = $session->getDescription();
        if (strlen($sessionTitle) > self::getLimitNotifyDetailLength()) {
            $sessionTitle = substr(strip_tags($sessionTitle, 'a'), 0, self::getLimitNotifyDetailLength()) . '...';
        }

        /**
         * @var Role $sessionRole
         */
        $roleIds = [];
        foreach ($session->getRestrictionAcces() as $sessionRole) {
            $roleIds[] = $sessionRole->getRole();
        }

        $this->processNotification(
            $now->format('YmdHis'),
            $moduleTitle,
            $sessionTitle . ' ' . $session->getFormateur()->getPrenomNom(),
            [
                'id' => $session->getModule()->getId(),
                'roleIds' => $roleIds,
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
        //Get the resource that matches training sessions url.
        /** @var Ressource $resource */
        $resource = $this->resourceManager->getRessourceMatchingUrl('/module/');

        //Filter training sessions allowed role (keep only those allowed to use training session resource).
        /** @var Acl[][] $acl */
        $grantedRoles = [];
        $acl = $this->aclManager->getAclByRessourceByRole();
        foreach ($acl as $roleId => $roleAcl) {
            foreach ($roleAcl as $resourceId => $resourceAcl) {
                if (in_array($resourceAcl->getRole()->getRole(), $notification->getData('roleIds'))) {
                    if ($resource->getId() === $resourceId) {
                        if ($resourceAcl->getRead()) {
                            $grantedRoles[] = $resourceAcl->getRole()->getRole();
                        }
                    }
                }
            }
        }

        //Finally get query builder of active users with those roles.
        return $this->userRepository->getUsersByRolesQueryBuilder($grantedRoles);
    }

    /**=
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        if (1 === $notification->getDetailLevel() || NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_STRAIGHT === $notification->getFrequency()) {
            $options['liste'] = $notification->getTitle() . ' ' . $notification->getData('dateSession') . ' ' . $notification->getDetail();
        } else {
            $options['liste'] = $notification->getTitle();
        }
        $this->mailManager->sendNextSessionsNotification($notification->getUser(), $options);
    }
}
