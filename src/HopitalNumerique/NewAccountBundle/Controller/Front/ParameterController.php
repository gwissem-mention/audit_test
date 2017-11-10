<?php

namespace HopitalNumerique\NewAccountBundle\Controller\Front;

use HopitalNumerique\NewAccountBundle\Service\UserNotificationsSettings;
use HopitalNumerique\UserBundle\Domain\Command\UpdateNotificationsSettingsCommand;
use HopitalNumerique\UserBundle\Domain\Command\UpdateNotificationsSettingsHandler;
use HopitalNumerique\UserBundle\Form\Type\NotificationsSettingsType;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $userParametersCommand = new UpdateUserParametersCommand();
        $form = $this->createForm(UserParametersType::class, $userParametersCommand);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(UpdateUserParametersCommandHandler::class)->handle($userParametersCommand);
            $this->addFlash('success', $this->get('translator')->trans('form.notification.success', [], 'user-parameters'));

            return $this->redirectToRoute('account_parameter');
        }

        $notificationSettingsCommand = new UpdateNotificationsSettingsCommand($this->getUser(), $settings, $schedules);
        $notificationsForm = $this->createForm(NotificationsSettingsType::class, $notificationSettingsCommand, [
            'action' => $this->generateUrl('account_parameter_notifications_settings_save'),
        ]);

        return $this->render('NewAccountBundle:parameter:parameter.html.twig', [
            'form' => $form->createView(),
            'notificationsForm' => $notificationsForm->createView(),
            'notifications' => [
                'sections' => $sections
            ],
            'page' => 'parameter-page',
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function saveNotificationsSettingsAction(Request $request)
    {
        list(, $settings, $schedules) = $this->get(UserNotificationsSettings::class)->retrieveUserSettings($this->getUser());

        $notificationSettingsCommand = new UpdateNotificationsSettingsCommand($this->getUser(), $settings, $schedules);
        $notificationsForm = $this->createForm(NotificationsSettingsType::class, $notificationSettingsCommand);

        $notificationsForm->handleRequest($request);
        if ($notificationsForm->isSubmitted() && $notificationsForm->isValid()) {
            $this->get(UpdateNotificationsSettingsHandler::class)->handle($notificationSettingsCommand);

            return new JsonResponse(null, 200);
        }

        return new JsonResponse(null, 418);
    }
}
