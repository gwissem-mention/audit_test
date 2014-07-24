<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;


class RechercheParcoursDetailsController extends Controller
{
    public function indexAction(RechercheParcours $rechercheParcours)
    {
        //Création du tableau des étapes pour lié un détail
        $etapes = array();
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 233));
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 234));
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 235));
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 236));
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 237));
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 226));

        //Récupération des étapes déjà sélectionnées sur la recherche de parcours
        $etapesSelected = array();
        foreach ($rechercheParcours->getRecherchesParcoursDetails() as $detail) 
        {
            $etapesSelected[] = $detail->getReference()->getId();
        }

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursDetails:index.html.twig', array(
                'etapes'            => $etapes,
                'etapesSelected'    => $etapesSelected,
                'rechercheParcours' => $rechercheParcours
            ));    
    }

    public function addAction(Request $request)
    {
        //Création du tableau des étapes pour lié un détail
        $etapes = array();
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 233));
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 234));
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 235));
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 236));
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 237));
        $etapes[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 226));

        //Récupération des étapes déjà sélectionnées sur la recherche de parcours
        $etapesSelected = array();
        foreach ($rechercheParcours->getRecherchesParcoursDetails() as $detail) 
        {
            $etapesSelected[] = $detail->getReference()->getId();
        }

        //créer un question
        $detail = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->createEmpty();

        $libelle           = $request->request->get('libelle');
        $rechercheParcours = $request->request->get('rechercheParcours');

        //Calcul de l'ordre
        $order             = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->countDetails() + 1;
        $rechercheParcours = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->findOneBy(array('id' => $rechercheParcours));

        $reponse->setOrder( $order );
        $reponse->setLibelle( $libelle );
        if($redirigeQuestion != null)
            $reponse->setRedirigeQuestion( $redirigeQuestion );
        $reponse->setAutreQuestion( $autreQuestion == 'true' ? true : false );
        $reponse->setQuestion( $question );

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->save( $reponse );

        $notes = array();

        foreach ($question->getReponses() as $reponseQuestion) 
        {
            //get ponderations
            $refsPonderees = $this->container->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
            $note          = is_null($reponseQuestion->getReferences()) ? 0 : $this->container->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $reponseQuestion->getReferences(), $refsPonderees );

            $notes[$reponseQuestion->getId()] = $note;
        }

        //return new Response('{"success":true}', 200;
        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:add.html.twig', array(
            'reponse'      => $reponse,
            'expBesoin'    => $question,
            'expBesoinAll' => $expBesoins,
            'notes'        => $notes
        ));  
    }

    public function editAction(Request $request)
    {
        //Récupération des données envoyées par la requete AJAX
        $idReponse       = $request->request->get('idReponse');
        $isAutreQuestion = $request->request->get('isAutreQuestion') == 'true' ? true : false;
        //Modification de la réponse
        $reponse = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->findOneBy(array('id' => $idReponse));

        if($isAutreQuestion)
        {
            //efface toutes les références
            $oldRefs = $this->get('hopitalnumerique_recherche.manager.refexpbesoinreponses')->findBy( array('expBesoinReponses' => $idReponse) );
            $this->get('hopitalnumerique_recherche.manager.refexpbesoinreponses')->delete( $oldRefs );

            //Set la question
            $idQuestion = $request->request->get('idQuestion');

            $question   = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findOneBy(array('id' => $idQuestion));
            $reponse->setRedirigeQuestion($question);
        }

        $reponse->setAutreQuestion( $isAutreQuestion );

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->save( $reponse );

        return new Response('{"success":true}', 200);   
    }

    public function deleteAction(Request $request)
    {
        //Récupération des données envoyées par la requete AJAX
        $idReponse       = $request->request->get('id');
        $reponse = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->findOneBy(array('id' => $idReponse));

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->delete( $reponse );

        return new Response('{"success":true}', 200); 
    }

    public function reorderAction()
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->reorder( $datas, null );
        $this->getDoctrine()->getManager()->flush();

        //return success.true si le fichier existe deja
        return new Response('{"success":true}', 200);    
    }

}
