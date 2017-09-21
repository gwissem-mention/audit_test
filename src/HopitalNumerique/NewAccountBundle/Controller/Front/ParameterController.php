<?php

namespace HopitalNumerique\NewAccountBundle\Controller\Front;

use HopitalNumerique\NewAccountBundle\Service\UserNotificationsSettings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Form\Type\UserParametersType;
use HopitalNumerique\UserBundle\Domain\Command\UpdateUserParametersCommand;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\UserBundle\Domain\Command\UpdateUserParametersCommandHandler;

/**
 * Class ParameterController
 */
class ParameterController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function parameterAction(Request $request)
    {
        /**
         * @var NotificationProviderAbstract[][] $sections
         */

        list($sections, $settings, $schedules) = $this->get(UserNotificationsSettings::class)->retrieveUserSettings($this->getUser());
        $userParametersCommand = new UpdateUserParametersCommand($this->getUser(), $settings, $schedules);
        $form = $this->createForm(UserParametersType::class, $userParametersCommand);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(UpdateUserParametersCommandHandler::class)->handle($userParametersCommand);
            $this->addFlash('success', $this->get('translator')->trans('form.notification.success', [], 'user-parameters'));

            return $this->redirectToRoute('account_parameter');
        }

        return $this->render('NewAccountBundle:parameter:parameter.html.twig', [
            'form' => $form->createView(),
            'notifications' => [
                'sections' => $sections
            ],
            'page' => 'parameter-page',
        ]);
    }
}
