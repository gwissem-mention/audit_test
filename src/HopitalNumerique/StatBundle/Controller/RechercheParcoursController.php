<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RechercheParcoursController extends Controller
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

        $fonctionsUser = $this->get('hopitalnumerique_reference.manager.reference')->findBy(array('code' => 'CONTEXTE_FONCTION_INTERNAUTE'), array('libelle' => 'ASC'));

        return $this->render('HopitalNumeriqueStatBundle:Back:partials/RechercheParcours/bloc.html.twig', array(
            'paramsFonct'   => $paramsFonct,
            'fonctionsUser' => $fonctionsUser
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
        $dateDebut    = $request->request->get('datedebut-rechercheParcours');
        $dateFin      = $request->request->get('dateFin-rechercheParcours');

        $perimFonctId = intval($request->request->get('perimFonctionnellesSelect'));
        $profilType   = $request->request->get('profilTypeSelect');
        $fonctionUser = $request->request->get('fonctionUserSelect');

        $res = $this->generationTableau($dateDebut , $dateFin, $perimFonctId, $profilType, $fonctionUser);
        
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/RechercheParcours/tableau.html.twig', array(
            'notesMoyenneParEtape' => $res['notesMoyenneParEtape'],
            'etapes'               => $res['etapes'],
            'entetesTableau'       => $res['entetesTableau']
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
        $dateDebut    = $request->request->get('datedebut-rechercheParcours');
        $dateFin      = $request->request->get('dateFin-rechercheParcours');
        $perimFonctId = intval($request->request->get('perimFonctionnellesSelect'));
        $profilType   = $request->request->get('profilTypeSelect');
        $fonctionUser = $request->request->get('fonctionUserSelect');

        $donneesTab = $this->generationTableau($dateDebut , $dateFin, $perimFonctId, $profilType, $fonctionUser);

        //Colonnes communes
        $colonnes = array( 
            'df'    => 'Domaine Fonctionnel',
            'id'    => 'ID Etape', 
            'etape' => 'Nom Etape',
        );

        //Récupération des références concernant le choix entre typeES et profil
        foreach ($donneesTab["entetesTableau"] as $key => $enteteTableau) 
        {
            $colonnes[$key] = $enteteTableau;
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');
        $datas         = $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->getDatasForExportByEtape( $donneesTab );

        return $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->exportCsv( $colonnes, $datas, 'export-recherche-parcours-par-etape.csv', $kernelCharset );
    }

    /**
     * Code appelé lors de la génération du tableau et de l'export CSV
     *
     * @return array
     */
    private function generationTableau($dateDebut , $dateFin, $perimFonctId, $profilType, $fonctionUser)
    {
        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);
        $entetesTableau    = array();

        // -- vv -- Récupération des objets -- vv --
        $references = array();
        $profils    = array();
        $typesES     = array();
        
        $perimFonct = ($perimFonctId === 0 ) ? null : $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $perimFonctId));

        $rechercheParcours = array();
        if(is_null($perimFonct))
        {
            $rechercheParcours = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->findAll();
        }
        else
        {
            $rechercheParcours[] = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->findOneBy(array('reference' => $perimFonct));
        }
        
        //Récupération des références liées aux Types ES ou aux profils
        if('typeES' === $profilType)
        {
            $typesES[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 267));
            $typesES[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 268));
            $typesES[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 269));
            $typesES[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 307));

            foreach ($typesES as $key => $typeES) 
            {
                if(!is_null($typeES))
                    $entetesTableau[$typeES->getId()] = $typeES->getLibelle();
            }
            
            $entetesTableau["NC"] = "NC";
        }
        elseif('profil' === $profilType)
        {
            $profils[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 283));
            $profils[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 284));
            $profils[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 285));

            foreach ($profils as $key => $profil) 
            {
                if(!is_null($profil))
                    $entetesTableau[$profil->getId()] = $profil->getLibelle();
            }

            $entetesTableau["NC"] = "NC";
        }

        //Récupération des notes triées par users
        $etapes = array();
        foreach ($rechercheParcours as $recherche) 
        {
            foreach ($recherche->getRecherchesParcoursDetails() as $etape) 
            {
                $etapes[$etape->getId()] = $etape;
            }
        }

        //Récupération de la note moyenne par étapes dans un tableau (étapeId => moyenne arrondie à l'entier)
        $notesMoyenneParEtape = $this->get('hopitalnumerique_recherche_parcours.manager.matrise_user')->getAverageAllEtapesAllUser($profilType, $fonctionUser);

        return array(
            'notesMoyenneParEtape' => $notesMoyenneParEtape,
            'etapes'               => $etapes,
            'entetesTableau'       => $entetesTableau
        );
    }
}
