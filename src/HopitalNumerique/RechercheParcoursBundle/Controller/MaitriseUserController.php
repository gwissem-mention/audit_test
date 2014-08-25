<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MaitriseUserController extends Controller
{

    /**
     * [saveAction description]
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function saveNoteAction( Request $request )
    {
        //Récupère l'utilisateur connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //Récupération des valeurs passées en param de la requête
        $rechercheParcoursDetails = $request->request->get('rechercheParcoursDetails');
        $idObjet                  = $request->request->get('idObjet');
        $valeurMatrise            = $request->request->get('value');

        //Récupération de l'entité MaitriseUser
        $note = $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->findOneBy(array('objet' => $idObjet, 'user' => $user, 'rechercheParcoursDetails' => $rechercheParcoursDetails));
        //Mise à jour de sa valeur
        $note->setPourcentageMaitrise($valeurMatrise);
        $note->setDateMaitrise(new \DateTime());

        //Sauvegarde de la modif
        $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->save( $note );

        return new Response('{"success":true}', 200);
    }

    /**
     * [saveAction description]
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function saveNonConcerneAction( Request $request )
    {
        //Récupère l'utilisateur connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //Récupération des valeurs passées en param de la requête
        $rechercheParcoursDetails = $request->request->get('rechercheParcoursDetails');
        $idObjet                  = $request->request->get('idObjet');
        $nonConcerne              = $request->request->get('value') === "checked";

        //Récupération de l'entité MaitriseUser
        $note = $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->findOneBy(array('objet' => $idObjet, 'user' => $user, 'rechercheParcoursDetails' => $rechercheParcoursDetails));
        //Mise à jour de sa valeur
        $note->setNonConcerne($nonConcerne);
        $note->setDateMaitrise(new \DateTime());

        //Sauvegarde de la modif
        $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->save( $note );

        return new Response('{"success":true}', 200);
    }
}