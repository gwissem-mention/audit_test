<?php
namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RechercheParcoursController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction(RechercheParcoursGestion $rechercheParcoursGestion)
    {
        $recherchesParcours = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->findBy(array('recherchesParcoursGestion' => $rechercheParcoursGestion), array('order' => 'ASC'));

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcours:Back/index.html.twig', array(
            'recherchesParcours' => $recherchesParcours
        ));
    }

    /**
     * Fenêtre d'édition.
     */
    public function editAction(RechercheParcours $rechercheParcours)
    {
        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcours:Back/edit.html.twig', [
            'rechercheParcours' => $rechercheParcours
        ]);
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
     * Met à jour l'ordre des différentes questions
     */
    public function reorderAction()
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->reorder( $datas );
        $this->getDoctrine()->getManager()->flush();

        return new Response('{"success":true}', 200);
    }

    // ----------- FRONT --------------

    /**
     * Index du front Action
     */
    public function indexFrontAction(Request $request, RechercheParcoursGestion $rechercheParcoursGestion)
    {
        $request->getSession()->set('urlToRedirect', $request->getUri());

        //Tableau des étapes du projet
        $etapes = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->findBy(array('recherchesParcoursGestion' => $rechercheParcoursGestion), array('order' => 'ASC'));

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcours:Front/index.html.twig', array(
            'etapes' => $etapes
        ));
    }
}