<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PointdurController extends Controller
{
    /**
     * Affiche les statistiques des points durs
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo™
     */
    public function indexAction( )
    {
        $paramsFonct = array();
        $paramsFonct[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 291));
        $paramsFonct[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 292));
        $paramsFonct[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 293));
        $paramsFonct[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 294));
        $paramsFonct[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 295));

        return $this->render('HopitalNumeriqueStatBundle:Back:partials/PointsDurs/bloc.html.twig', array(
            'paramsFonct' => $paramsFonct
        ));
    }

    /**
     * Génération du tableau à exporter
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * 
     * @return View
     */
    public function generateTableauAction( Request $request )
    {
        //Récupération de la requete
        $dateDebut    = $request->request->get('datedebut');
        $dateFin      = $request->request->get('dateFin');
        $perimFonctId = intval($request->request->get('perimFonctionnellesSelect'));
        $profilType   = $request->request->get('profilTypeSelect');

        $res = $this->generationTableau($dateDebut , $dateFin, $perimFonctId, $profilType);
        
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/PointsDurs/tableau.html.twig', array(
            'notes'          => $res['notes'],
            'pointsDur'      => $res['pointsDur'],
            'entetesTableau' => $res['entetesTableau']
        ));
    }

    /**
     * Génération du tableau à exporter
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * 
     * @return View
     */
    public function exportCSVAction( Request $request )
    {
        //Récupération de la requete
        $dateDebut    = $request->request->get('datedebut');
        $dateFin      = $request->request->get('dateFin');
        $perimFonctId = intval($request->request->get('perimFonctionnellesSelect'));
        $profilType   = $request->request->get('profilTypeSelect');

        $donneesTab = $this->generationTableau($dateDebut , $dateFin, $perimFonctId, $profilType);

        //Colonnes communes
        $colonnes = array( 
            'id'    => 'ID publication', 
            'titre' => 'Titre publication',
        );

        //Récupération des références concernant le choix entre typeES et profil
        foreach ($donneesTab["entetesTableau"] as $key => $enteteTableau) 
        {
            $colonnes[$key] = $enteteTableau;
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');
        $datas         = $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->getDatasForExport( $donneesTab );

        return $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->exportCsv( $colonnes, $datas, 'export-points-dur.csv', $kernelCharset );
    }

    /**
     * Code appelé lors de la génération du tableau et de l'export CSV
     *
     * @return array
     */
    private function generationTableau($dateDebut , $dateFin, $perimFonctId, $profilType)
    {
        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);
        $entetesTableau    = array();

        // -- vv -- Récupération des objets -- vv --
        $references       = array();
        $referencesTemp   = array();
        $referencesTemp[] = $perimFonct = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $perimFonctId));
        //Récupération du parent du périmetre fonctionnelle
        $referencesTemp[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $perimFonct->getParent()->getId()));

        //Récupération des références liées aux Types ES ou aux profils
        if('typeES' === $profilType)
        {
            $referencesTemp[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 222));
            $referencesTemp[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 266));
            $referencesTemp[] = $refTemp2 = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 267));
            $referencesTemp[] = $refTemp3 = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 268));
            $referencesTemp[] = $refTemp4 = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 269));
            $referencesTemp[] = $refTemp5 = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 307));

            $entetesTableau[$refTemp2->getId()] = $refTemp2->getLibelle();
            $entetesTableau[$refTemp3->getId()] = $refTemp3->getLibelle();
            $entetesTableau[$refTemp4->getId()] = $refTemp4->getLibelle();
            $entetesTableau[$refTemp5->getId()] = $refTemp5->getLibelle();
            $entetesTableau["NC"]               = "NC";
        }
        elseif('profil' === $profilType)
        {
            $referencesTemp[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 222));
            $referencesTemp[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 277));
            $referencesTemp[] = $refTemp  = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 283));
            $referencesTemp[] = $refTemp2 = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 284));
            $referencesTemp[] = $refTemp3 = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 285));

            $entetesTableau[$refTemp->getId()]  = $refTemp->getLibelle();
            $entetesTableau[$refTemp2->getId()] = $refTemp2->getLibelle();
            $entetesTableau[$refTemp3->getId()] = $refTemp3->getLibelle();
            $entetesTableau["NC"]               = "NC";
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
        $role          = $this->get('nodevo_role.manager.role')->findOneBy(array('role' => 'ROLE_ADMINISTRATEUR_1'));
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $objets        = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $references, $role, $refsPonderees );

        $pointsDur = array();

        foreach ($objets as $objet) 
        {
            if("point-dur" === $objet["categ"])
                $pointsDur[$objet['id']] = $objet;
        }
        // ^^ -- Récupération des objets -- ^
        
        ksort($pointsDur);
        
        //Récupération des notes
        $notes = $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->getAllNotesSortByObjet($profilType, $dateDebutDateTime, $dateFinDateTime);

        return array(
            'notes'          => $notes,
            'pointsDur'      => $pointsDur,
            'entetesTableau' => $entetesTableau
        );
    }
}
