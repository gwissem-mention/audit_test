<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;
use Nodevo\ToolsBundle\Tools\Chaine;

class RechercheParcoursDetailsController extends Controller
{
    public function indexAction(RechercheParcours $rechercheParcours)
    {
        //Récupération des étapes déjà sélectionnées sur la recherche de parcours
        $etapesSelected = [];
        foreach ($rechercheParcours->getRecherchesParcoursDetails() as $detail) {
            $etapesSelected[] = $detail->getReference()->getId();
        }

        //Création du tableau des étapes pour lié un détail
        $etapes = $rechercheParcours->getRecherchesParcoursGestion()->getReferencesVentilations();

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursDetails:index.html.twig', [
                'etapes' => $etapes,
                'etapesSelected' => $etapesSelected,
                'rechercheParcours' => $rechercheParcours,
            ]);
    }

    public function addAction(Request $request, RechercheParcours $rechercheParcours)
    {
        //Récupération de l'id de l'étape sélectionnée dans la select
        $etapeId = $request->request->get('etape');
        $etape = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => $etapeId]);

        //créer du détails
        $detail = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->createEmpty();

        $libelle = $request->request->get('libelle');
        //Calcul de l'ordre
        $order = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->countDetails() + 1;

        $detail->setOrder($order);
        $detail->setReference($etape);
        $detail->setDescription('');
        $detail->setRechercheParcours($rechercheParcours);

        //save
        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->save($detail);

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursDetails:add.html.twig', [
                'details' => $detail,
        ]);
    }

    public function deleteAction(Request $request)
    {
        //Récupération des données envoyées par la requete AJAX
        $idDetails = $request->request->get('id');
        $details = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->findOneBy(['id' => $idDetails]);
        $refLibelle = $details->getReference()->getLibelle();
        $refId = $details->getReference()->getId();

        //save
        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->delete($details);

        $reponse = json_encode(['success' => true, 'refLibelle' => $refLibelle, 'refId' => $refId]);

        return new Response($reponse, 200);
    }

    public function reorderAction()
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->reorder($datas, null);
        $this->getDoctrine()->getManager()->flush();

        //return success.true si le fichier existe deja
        return new Response('{"success":true}', 200);
    }

    /**
     * [editFancyAction description].
     *
     * @param RechercheParcoursDetails $rechercheParcoursDetails [description]
     *
     * @return Fancy
     */
    public function editFancyAction(RechercheParcoursDetails $rechercheParcoursDetails)
    {
        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursDetails:edit.html.twig', [
            'rechercheParcoursDetails' => $rechercheParcoursDetails,
        ]);
    }

    /**
     * [editSaveAction description].
     *
     * @param RechercheParcoursDetails $rechercheParcoursDetails [description]
     *
     * @return Response
     */
    public function editSaveAction(Request $request, RechercheParcoursDetails $rechercheParcoursDetails)
    {
        $rechercheParcoursDetails->setDescription($request->request->get('description'));
        $rechercheParcoursDetails->setShowChildren($request->request->get('showChild') == 'true');

        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->save($rechercheParcoursDetails);

        return new Response('{"success":true}', 200);
    }
}
