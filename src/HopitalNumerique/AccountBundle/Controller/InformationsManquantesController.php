<?php

namespace HopitalNumerique\AccountBundle\Controller;

use HopitalNumerique\UserBundle\Form\Type\User\InformationsManquantesType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InformationsManquantesController
 */
class InformationsManquantesController extends Controller
{
    /**
     * Formulaire des infos manquantes à l'inscription à la communauté de pratique.
     *
     * @return Response
     */
    public function communautePratiqueAction()
    {
        return $this->render('HopitalNumeriqueAccountBundle:InformationsManquantes:form.html.twig', [
            'form' => $this->createForm(
                'nodevouser_user_informationsmanquantes',
                $this->getUser(),
                ['informations_type' => InformationsManquantesType::TYPE_COMMUNAUTE_PRATIQUE]
            )->createView(),
        ]);
    }

    /**
     * Formulaire des infos manquantes à la demande d'intervention d'un ambassadeur.
     *
     * @return Response
     */
    public function demandeInterventionAction()
    {
        return $this->render('HopitalNumeriqueAccountBundle:InformationsManquantes:form.html.twig', [
            'form' => $this->createForm(
                'nodevouser_user_informationsmanquantes',
                $this->getUser(),
                ['informations_type' => InformationsManquantesType::TYPE_DEMANDE_INTERVENTION]
            )->createView(),
        ]);
    }

    /**
     * Sauvegarde le formulaire.
     *
     * @param Request $request
     * @param int     $informationsType Type des informations
     *
     * @return RedirectResponse
     */
    public function saveAction(Request $request, $informationsType)
    {
        $user = $this->getUser();
        $form = $this->createForm(
            'nodevouser_user_informationsmanquantes',
            $user,
            ['informations_type' => intval($informationsType)]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->container->get('hopitalnumerique_user.manager.user')->save($user);
            $this->addFlash('success', 'Informations enregistrées.');

            if (InformationsManquantesType::TYPE_COMMUNAUTE_PRATIQUE == $informationsType) {
                if (null !== $user && !$this->container
                        ->get('hopitalnumerique_communautepratique.dependency_injection.inscription')
                        ->hasInformationManquante($user)) {
                    if (!$user->isInscritCommunautePratique()) {
                        $user->setInscritCommunautePratique(true);
                        $this->container->get('hopitalnumerique_user.manager.user')->save($user);
                        $this->addFlash('success', 'L\'inscription à la communauté de pratique a été confirmée.');
                    }

                    return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_accueil_index'));
                } else {
                    $this->addFlash('danger', 'L\'inscription à la communauté de pratique a échoué.');

                    return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
                }
            }
        }

        return $this->redirect($form->get('redirection')->getData());
    }
}
