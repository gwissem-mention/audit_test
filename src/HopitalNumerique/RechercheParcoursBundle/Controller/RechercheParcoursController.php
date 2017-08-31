<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Event\GuidedSearchUpdatedEvent;
use HopitalNumerique\RechercheParcoursBundle\Events;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RechercheParcoursController extends Controller
{
    /**
     * Index Action.
     */
    public function indexAction(RechercheParcoursGestion $rechercheParcoursGestion)
    {
        $form = $this->createForm('hopitalnumerique_rechercheparcours_rechercheparcoursgestion_history', null, [
            'action' => $this->generateUrl('hopital_numerique_recherche_parcours_savenotification', [
                'rechercheParcoursGestion' => $rechercheParcoursGestion->getId()
            ])
        ]);

        $serviceParcours = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours');
        $serviceHistory = $this->get('hopitalnumerique\rechercheparcoursbundle\service\guidedsearchhistoryreader');

        $recherchesParcours = $serviceParcours->findBy(['recherchesParcoursGestion' => $rechercheParcoursGestion], ['order' => 'ASC']);

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcours:Back/index.html.twig', [
            'recherchesParcours' => $recherchesParcours,
            'lastNotification' => $serviceHistory->lastNotification($rechercheParcoursGestion),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Fenêtre d'édition.
     */
    public function editAction(RechercheParcours $rechercheParcours)
    {
        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcours:Back/edit.html.twig', [
            'rechercheParcours' => $rechercheParcours,
        ]);
    }

    /**
     * Save a new notification for guided search
     *
     * @param Request $request
     * @param RechercheParcoursGestion $rechercheParcoursGestion
     *
     * @return JsonResponse
     */
    public function saveNotificationAction(Request $request, RechercheParcoursGestion $rechercheParcoursGestion)
    {
        $notify = $request->get('update_notify');
        $reason = $request->get('reason');
        $this->get('hopitalnumerique\rechercheparcoursbundle\service\guidedsearchhistorywriter')->create(
            $rechercheParcoursGestion,
            $this->getUser(),
            $notify,
            $reason
        );

        $message = $this->get('translator')->trans('notifications.save.message', [], 'guided_search');

        return new JsonResponse(['message' => $message]);
    }

    /**
     * Enregistre le parcours.
     */
    public function saveAction(Request $request, RechercheParcours $rechercheParcours)
    {
        $rechercheParcours->setDescription($request->request->get('description'));

        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->save($rechercheParcours);

        return new JsonResponse(['success' => true]);
    }

    /**
     * Met à jour l'ordre des différentes questions.
     */
    public function reorderAction()
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->reorder($datas);
        $this->getDoctrine()->getManager()->flush();

        return new Response('{"success":true}', 200);
    }
}
