<?php
namespace HopitalNumerique\AccountBundle\Controller;

use HopitalNumerique\UserBundle\Form\Type\User\InformationsManquantesType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class InformationsManquantesController extends Controller
{
    /**
     * Formulaire des infos manquantes à l'inscription à la communauté de pratique.
     */
    public function communautePratiqueAction(Request $request)
    {
        return $this->render('HopitalNumeriqueAccountBundle:InformationsManquantes:form.html.twig', [
            //'form' => $this->container->get('hopitalnumerique_communautepratique.dependency_injection.inscription')->getInformationsManquantesForm($this->getUser())->createView()
            'form' => $this->createForm('nodevouser_user_informationsmanquantes', $this->getUser(), ['informations_type' => InformationsManquantesType::TYPE_COMMUNAUTE_PRATIQUE])->createView()
        ]);
    }

    /**
     * Sauvegarde le formulaire.
     *
     * @param integer $informationsType Type des informations
     */
    public function saveAction(Request $request, $informationsType)
    {
        $user = $this->getUser();
        $form = $this->createForm('nodevouser_user_informationsmanquantes', $user, ['informations_type' => intval($informationsType)]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->container->get('hopitalnumerique_user.manager.user')->save($user);
            $this->addFlash('success', 'Informations enregistrées.');
        }

        return $this->redirect($form->get('redirection')->getData());
    }
}
