<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;

class RechercheParcoursDetailsController extends Controller
{
    public function indexAction(RechercheParcours $rechercheParcours)
    {
        //Récupération des étapes déjà sélectionnées sur la recherche de parcours
        $etapesSelected = array();
        foreach ($rechercheParcours->getRecherchesParcoursDetails() as $detail) 
        {
            $etapesSelected[] = $detail->getReference()->getId();
        }

        //Création du tableau des étapes pour lié un détail
        $etapes = array();
        if(!in_array(234, $etapesSelected))
            $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 234));
        if(!in_array(237, $etapesSelected))
            $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 237));
        if(!in_array(235, $etapesSelected))
            $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 235));
        if(!in_array(236, $etapesSelected))
            $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 236));
        if(!in_array(233, $etapesSelected))
            $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 233));
        if(!in_array(226, $etapesSelected))
            $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 226));

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursDetails:index.html.twig', array(
                'etapes'            => $etapes,
                'etapesSelected'    => $etapesSelected,
                'rechercheParcours' => $rechercheParcours
            ));    
    }

    public function addAction(Request $request, RechercheParcours $rechercheParcours)
    {
        //Récupération de l'id de l'étape sélectionnée dans la select
        $etapeId = $request->request->get('etape');
        $etape   = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $etapeId) );

        //créer du détails
        $detail = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->createEmpty();

        $libelle = $request->request->get('libelle');
        //Calcul de l'ordre
        $order   = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->countDetails() + 1;

        $detail->setOrder( $order );
        $detail->setReference( $etape );
        $detail->setDescription('');
        $detail->setRechercheParcours($rechercheParcours);

        //save
        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->save( $detail );

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursDetails:add.html.twig', array(
                'details' => $detail,
        ));  
    }

    public function deleteAction(Request $request)
    {
        //Récupération des données envoyées par la requete AJAX
        $idDetails = $request->request->get('id');
        $details   = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->findOneBy(array('id' => $idDetails));
        $refLibelle   = $details->getReference()->getLibelle();
        $refId        = $details->getReference()->getId();

        //save
        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->delete( $details );

        $reponse = json_encode(array("success" => true, "refLibelle" => $refLibelle, "refId" => $refId));

        return new Response($reponse, 200); 
    }

    public function reorderAction()
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->reorder( $datas, null );
        $this->getDoctrine()->getManager()->flush();

        //return success.true si le fichier existe deja
        return new Response('{"success":true}', 200);    
    }

    /**
     * [editFancyAction description]
     *
     * @param  RechercheParcoursDetails  $rechercheParcoursDetails [description]
     *
     * @return Fancy
     */
    public function editFancyAction( RechercheParcoursDetails $rechercheParcoursDetails )
    {   
        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursDetails:edit.html.twig', array(
            'rechercheParcoursDetails' => $rechercheParcoursDetails
        ));
    }

    /**
     * [editSaveAction description]
     *
     * @param  RechercheParcoursDetails  $rechercheParcoursDetails [description]
     *
     * @return Response
     */
    public function editSaveAction( Request $request, RechercheParcoursDetails $rechercheParcoursDetails )
    {
        $rechercheParcoursDetails->setDescription($request->request->get('description'));

        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->save( $rechercheParcoursDetails );

        return new Response('{"success":true}', 200);
    }

}
