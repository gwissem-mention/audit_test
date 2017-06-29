<?php

namespace HopitalNumerique\NewAccountBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\NewAccountBundle\Service\ProfileCompletionCalculator;

/**
 * Class ProfileCompletionWidget
 */
class ProfileCompletionWidget extends WidgetAbstract
{
    /**
     * @var ProfileCompletionCalculator $profileCompletionCalculator
     */
    protected $profileCompletionCalculator;

    /**
     * @param ProfileCompletionCalculator $profileCompletionCalculator
     */
    public function setProfileCompletionCalculator(ProfileCompletionCalculator $profileCompletionCalculator)
    {
        $this->profileCompletionCalculator = $profileCompletionCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $completionRate = $this->profileCompletionCalculator->calculateForUser($user);

        $html = $this->twig->render('NewAccountBundle:profile:widget/profile.html.twig', [
            'user' => $user,
            'completion' => $completionRate,
            'firstTabToComplete' => $this->profileCompletionCalculator->getFirstTabToCompleteForUser($user),
        ]);

        $title = $this->translator->trans('title', [], 'widget');

        return new Widget('profile', $title, $html, $completionRate < 100);
    }
}
