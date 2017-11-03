<?php

namespace HopitalNumerique\NewAccountBundle\Service\UserData;

use HopitalNumerique\ExpertBundle\Manager\ActiviteExpertManager;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Security\InformationsAccessVoter;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetInterface;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ActivityMonitoringWidget
 */
class ActivityMonitoringWidget implements WidgetInterface, DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ReferenceManager
     */
    protected $referenceManager;

    /**
     * @var ActiviteExpertManager
     */
    protected $activiteExpertManager;

    /**
     * ActivityMonitoringWidget constructor.
     *
     * @param \Twig_Environment $twig
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TranslatorInterface $translator
     * @param ReferenceManager $referenceManager
     * @param ActiviteExpertManager $activiteExpertManager
     */
    public function __construct(
        \Twig_Environment $twig,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        TranslatorInterface $translator,
        ReferenceManager $referenceManager,
        ActiviteExpertManager $activiteExpertManager
    ) {
        $this->twig = $twig;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->translator = $translator;
        $this->referenceManager = $referenceManager;
        $this->activiteExpertManager = $activiteExpertManager;
    }

    /**
     * @return Widget|null
     */
    public function getWidget()
    {
        if (!$this->authorizationChecker->isGranted(InformationsAccessVoter::ACCESS_PAYMENTS_ACTIVITY)) {
            return null;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $montantVacation = intval($this->referenceManager->findOneById(560)->getLibelle());
        $tabActivities = $this->getActivities($user);

        return new Widget(
            'activity',
            $this->translator->trans('account.profile.activity_monitoring'),
            $this->twig->render('@NewAccount/profile/tabs/activity_monitoring.html.twig', [
                'activites' => $tabActivities['activities'],
                'totalVacation' => $tabActivities['totalVacation'],
                'montantVacation' => $montantVacation,
                'evenementVacations' => $tabActivities['eventVacations'],
            ])
        );
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function getActivities(User $user)
    {
        $totalVacation = [];
        $eventVacations = [];
        $activities = [];
        $isExpert = false;

        if ($this->authorizationChecker->isGranted(InformationsAccessVoter::IS_EXPERT)) {
            $activities = $this->activiteExpertManager->getActivitesForExpert($user->getId());
            $isExpert   = true;
        } elseif ($this->authorizationChecker->isGranted(InformationsAccessVoter::IS_ANAP_ADMIN)) {
            $activities = $this->activiteExpertManager->getActivitesForAnapien($user->getId());
        }

        foreach ($activities as $activity) {
            $totalVacation[$activity->getId()] = 0;

            foreach ($activity->getEvenements() as $event) {
                foreach ($event->getExperts() as $expert) {
                    if ($isExpert) {
                        //Récupération des données de l'expert courant uniquement
                        if ($expert->getExpertConcerne()->getId() !== $user->getId()) {
                            continue;
                        }
                    }

                    if (!array_key_exists($event->getId(), $eventVacations)) {
                        $eventVacations[$event->getId()] = 0;
                    }

                    if ($expert->getPresent()) {
                        ++$eventVacations[$event->getId()];
                        $totalVacation[$activity->getId()] += $event->getNbVacation();
                    }
                }
            }
        }

        return [
            'activities' => $activities,
            'totalVacation' => $totalVacation,
            'eventVacations' => $eventVacations,
        ];
    }
}