<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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

    // ----------- FRONT --------------

    /**
     * Index du front Action
     *
     * @param int     $id      Identifiant de la recherche par parcours
     * @param string  $alias   Alias de l'identifiant par parcours
     * @param int     $idEtape Identifiant de l'étape sélectionnée si il y en a une, sinon -1
     * @param string  $etape   Alias de l'étape sélectionné
     *
     * @return [type]
     */
    public function indexFrontAction($id, $alias, $idEtape, $etape )
    {
        //Récup!ère la recherche par parcours passé en param
        $rechercheParcours   = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->findOneBy( array( 'id' => $id ) );

        //On récupère l'user connecté et son rôle
        $user = $this->get('security.context')->getToken()->getUser();
        $role = $this->get('nodevo_role.manager.role')->getUserRole($user);

        //Vérifie si une étape a été spécifiée ou récupère la première étape de la recherche par parcours sélectionnée.
        if($idEtape !== 0 
            && in_array($idEtape, $rechercheParcours->getRecherchesParcoursDetailsIds()))
        {
             $rechercheParcoursDetails = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->findOneBy( array( 'id' => $idEtape ));
        }
        else
        {
            //Si aucune étape n'est spécifiée et pas d'user connecté on affiche la premier qu'on trouve pour cette recherche par parcours
            if('anon.' === $user)
            {
                $etapes = $rechercheParcours->getRecherchesParcoursDetails();
            }
            //Si un utilisateur est connecté on cherche la premiere qui n'a pas une moyenne de 100%
            else
            {
                $idEtapeMoyenne = 0;
                //Récupération de la note moyenne par étapes dans un tableau (étapeId => moyenne arrondie à l'entier)
                $notesMoyenneParEtape = $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->getAverage( $rechercheParcours, $user );
                foreach ($notesMoyenneParEtape as $key => $noteMoyenne)
                {
                    //Récupération de la premiere note pas à 100%
                    if($noteMoyenne !== 100)
                    {
                        $idEtapeMoyenne = $key;
                        break;
                    }
                    else
                    {
                        $idEtapeMoyenne = $idEtapeMoyenne === 0 ? $key : $idEtapeMoyenne;
                    }
                }
                $etapes = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours_details')->findBy( array( 'id' => $idEtapeMoyenne ));
                $etapes = empty($etapes) ? $rechercheParcours->getRecherchesParcoursDetails() : $etapes; 
            
            }

            //Si il y a bien des étapes à la recherche par parcours on récupère la 1ere
            if( !is_null($etapes)
                && isset($etapes)
                && !empty($etapes)
                && !is_null($etapes[0]) )
            {
                $idEtape      = $etapes[0]->getId();
                $tool         = new Chaine( $etapes[0]->getReference()->getLibelle() );
                $etapeLibelle = $tool->minifie();

                return $this->redirect( $this->generateUrl('hopital_numerique_recherche_parcours_details_index_front', array('id' => intval($id), 'alias' => $alias, 'idEtape' => $idEtape, 'etape' => $etapeLibelle) ) );
                
            }
            //Sinon on retourne sur la sélection de la recherche par parcours + message à l'utilisateur
            else
            {
                $this->get('session')->getFlashBag()->add( 'danger' , 'Il n\'existe aucune étape pour cette rubrique.');
                return $this->redirect( $this->generateUrl('hopital_numerique_recherche_parcours_homepage_front' ) );
            }
        }

        //Récupération des références + Tri pour l'affichage des points dur
        $referenceRechercheParcours        = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => intval($rechercheParcours->getReference()->getId()) ) );
        $referenceRechercheParcoursDetails = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => intval($rechercheParcoursDetails->getReference()->getId()) ) );

        $referencesTemp = array();
        $referencesTemp[] = $referenceRechercheParcours;
        $referencesTemp[] = $referenceRechercheParcoursDetails;


        //Récupération des infos de l'utilisateur, si il y en a un connecté, pour ajouter les filtres "Etablissement" et "Métier"
        if('anon.' !== $user)
        {
            //Type d'établissement
            if(!is_null($user->getStatutEtablissementSante()))
               $referencesTemp[] =  $user->getStatutEtablissementSante();

            //Métier internaute
            if(!is_null($user->getProfilEtablissementSante()))
               $referencesTemp[] =  $user->getProfilEtablissementSante();
                
        }

        $refChilds = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 
                        'parent' => intval( $rechercheParcoursDetails->getReference()->getId() )
                    ));
        foreach ( $refChilds as $refChild)
        {
            $referencesTemp[] = $refChild;
        }

        //Parcourt les références de la réponse, puis les tris pour l'affichage de la recherche
        foreach ($referencesTemp as $reference) 
        {
            //Récupère la référence courante
            $referenceTemp = $reference;

            //Récupère le premier parent
            while(!is_null($referenceTemp->getParent())
                    && $referenceTemp->getParent()->getId() != null)
            {
                $referenceTemp = $referenceTemp->getParent();
            }

            //Trie la référence dans la bonne catégorie
            switch ($referenceTemp->getId()) 
            {
                case 220:
                    $references['categ1'][] = $reference->getId();
                    break;
                case 221:
                    $references['categ2'][] = $reference->getId();
                    break;
                case 223:
                    $references['categ3'][] = $reference->getId();
                    break;
                case 222:
                    $references['categ4'][] = $reference->getId();
                    break;
            }
        }

        //Récupérations
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $objets        = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $references, $role, $refsPonderees );
        $objets        = $this->get('hopitalnumerique_objet.manager.consultation')->updateObjetsWithConnectedUser( $objets, $user );

        

        //En mode connecté
        if('anon.' !== $user)
        {
            //Récupération des notes existante sur les points dur pour l'utilisateur courant 
            $notes = $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->getAllOrderedByPointDurForParcoursEtape( $user, $rechercheParcoursDetails->getId() );

            foreach ($objets as $objet) 
            {
                if("point-dur" === $objet["categ"]
                    && $objet['primary']
                    && !array_key_exists($objet['id'], $notes))
                {
                    //Si il n'y a pas encore de note pour ce point dur, dans cette étape associé à l'utilisateur courant alors on le créé.
                    $note = $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->createEmpty();
                    $note->setRechercheParcoursDetails($rechercheParcoursDetails);
                    $note->setPourcentageMaitrise(0);
                    $note->setObjet( $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $objet['id']) ) );
                    $note->setUser($user);

                    $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->save( $note );

                    //Puis on l'ajoute aux notes
                    $notes[$objet['id']] = $note;
                }
            }

            //Dans le cas où des points-dur ont une date dépassée, changement de groupe de restriction ou passé inactif
            $notes = $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->cleanNotesByObjet( $notes, $objets );

            $notesJSON = array();
            //Set d'un tableau de note en JSOn pour le chargement des slides
            foreach ($notes as $key => $note) 
            {
                $notesJSON[$key] = $note->getPourcentageMaitrise();
            }

            //Récupération de la note moyenne par étapes dans un tableau (étapeId => moyenne arrondie à l'entier)
            $notesMoyenneParEtape = $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->getAverage( $rechercheParcours, $user );
        }
        //Mode non connecté : pas de notes
        else
        {
            $notes                = array();
            $notesJSON            = json_encode(array('id' => 0));
            $notesMoyenneParEtape = array();
        }

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursDetails:Front/index.html.twig', array(
            'rechercheParcours'    => $rechercheParcours,
            'etapesSelected'       => $rechercheParcoursDetails,
            'objets'               => $objets,
            'notes'                => $notes,
            'notesJSON'            => json_encode($notesJSON),
            'notesMoyenneParEtape' => $notesMoyenneParEtape
        ));
    }

}
