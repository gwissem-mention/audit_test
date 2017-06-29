<?php

namespace HopitalNumerique\NewAccountBundle\Controller\Front;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Form\Type\UserParametersType;
use HopitalNumerique\UserBundle\Domain\Command\UpdateUserParametersCommand;

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
        $userParametersCommand = new UpdateUserParametersCommand($this->getUser());

        $form = $this->createForm(UserParametersType::class, $userParametersCommand);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('hopitalnumerique_user.update_user_parameters.command_handler')->handle($userParametersCommand);
            $this->addFlash('success', $this->get('translator')->trans('form.notification.success', [], 'user-parameters'));

            return $this->redirectToRoute('account_parameter');
        }

        return $this->render('NewAccountBundle:parameter:parameter.html.twig', [
            'form' => $form->createView(),
            'page' => 'parameter-page',
        ]);
    }
}
