<?php

namespace HopitalNumerique\UserBundle\Service\Widget;

use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntryRepository;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Model\Widget\WidgetExtension;
use HopitalNumerique\ObjetBundle\Repository\ConsultationRepository;
use HopitalNumerique\RechercheBundle\Repository\RequeteRepository;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use HopitalNumerique\UserBundle\Service\ActiveMemberCalculator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class Activity
 */
class ActivityWidget extends WidgetAbstract
{
    /**
     * @var RequeteRepository $requestRepository
     */
    protected $requestRepository;

    /**
     * @var ConsultationRepository $consultationRepository
     */
    protected $consultationRepository;

    /**
     * @var AutodiagEntryRepository $autodiagEntryRepository
     */
    protected $autodiagEntryRepository;

    /**
     * @var ActiveMemberCalculator $activeMemberCalculator
     */
    protected $activeMemberCalculator;

    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;


    /**
     * ActivityWidget constructor.
     *
     * @param \Twig_Environment $twig
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     * @param ActiveMemberCalculator $activeMemberCalculator
     */
    public function __construct(\Twig_Environment $twig, TokenStorageInterface $tokenStorage, TranslatorInterface $translator, ActiveMemberCalculator $activeMemberCalculator)
    {
        parent::__construct($twig, $tokenStorage, $translator);

        $this->activeMemberCalculator = $activeMemberCalculator;
    }

    /**
     * @param RequeteRepository $requestRepository
     * @param ConsultationRepository $consultationRepository
     * @param AutodiagEntryRepository $autodiagEntryRepository
     * @param UserRepository $userRepository
     */
    public function setRepositories(
        RequeteRepository $requestRepository,
        ConsultationRepository $consultationRepository,
        AutodiagEntryRepository $autodiagEntryRepository,
        UserRepository $userRepository
    ) {
        $this->requestRepository = $requestRepository;
        $this->consultationRepository = $consultationRepository;
        $this->autodiagEntryRepository = $autodiagEntryRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $activity = $this->activeMemberCalculator->getMemberActivity($user);

        $html = $this->twig->render('HopitalNumeriqueUserBundle:widget:activity.html.twig', [
            'savedSearchesCount' => $this->requestRepository->countSavedRequestForUser($user),
            'objectsReadedCount' => $this->consultationRepository->countViewsForUser($user),
            'forumPostCount' => $activity['nbPost'],
            'commentsCount' => $activity['nbComment'] + $activity['nbNote'],
            'autodiagsCount' => $this->autodiagEntryRepository->countActiveForUser($user),
            'recommendationsLogCount' => $activity['recommendationsCount'],
        ]);

        $title = $this->translator->trans('activity.title', [], 'widget');

        $widget = new Widget('user-activity', $title, $html);

        $widget->addExtension($this->createActivityRatioWidgetExtension($user));

        return $widget;
    }

    /**
     * @param User $user
     *
     * @return WidgetExtension
     */
    private function createActivityRatioWidgetExtension(User $user)
    {
        $activity = $this->activeMemberCalculator->getMemberActivity($user);

        $html = $this->twig->render('HopitalNumeriqueUserBundle:widget:activity_extension.html.twig', [
            'activityInfo' => $activity,
        ]);

        return new WidgetExtension('activity-ratio', $html);
    }
}
