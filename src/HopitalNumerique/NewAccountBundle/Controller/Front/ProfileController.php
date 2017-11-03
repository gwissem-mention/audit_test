<?php

namespace HopitalNumerique\NewAccountBundle\Controller\Front;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Form\Type\UserAccountType;
use Nodevo\RoleBundle\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProfileController
 */
class ProfileController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function profileAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $rolesContract = [
            Role::$ROLE_EXPERT_LABEL,
            Role::$ROLE_AMBASSADEUR_LABEL,
        ];

        $contracts = $this->get('hopitalnumerique_user.repository.contractualisation')->findByUser($user->getId());

        $missingInformation = $this->get('hopitalnumerique_communautepratique.dependency_injection.inscription')
           ->getMissingInformationByTab($user)
        ;

        $form = $this->createForm(UserAccountType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $this->get('translator')->trans('account.message.save.success'));

            return $this->redirectToRoute('account_profile');
        }

        return $this->render('NewAccountBundle:profile:profile.html.twig', [
            'form'               => $form->createView(),
            'user'               => $user,
            'missingInformation' => $missingInformation,
            'page'               => 'profile-page',
            'rolesContract'      => $rolesContract,
            'contracts'          => $contracts,
        ]);
    }
}
