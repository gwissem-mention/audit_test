<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ItemRequeteController extends Controller
{

    /* ----  Item de Requête  ---- */

    /**
     * Affiche les statistiques des items de requete
     */
    public function indexAction( )
    {
        $modelsReferencement = $this->get('hopitalnumerique_reference.manager.reference')->findBy(array('code' => 'CATEGORIES_RECHERCHE'), array('order' => 'ASC'));
        $categsContexte      = $this->get('hopitalnumerique_reference.manager.reference')->findByParent($this->get('hopitalnumerique_reference.manager.reference')->findOneById(222));

        return $this->render('HopitalNumeriqueStatBundle:Back:partials/ItemRequete/bloc.html.twig', array(
            'modelsReferencement' => $modelsReferencement,
            'categsContexte'      => $categsContexte
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
        $dateDebut    = $request->request->get('datedebut-itemRequete');
        $dateFin      = $request->request->get('dateFin-itemRequete');

        $modelReferencementId = intval($request->request->get('categorieModeleReferencementItemRequeteSelect'));
        $contexteId           = intval($request->request->get('categorieContexteItemRequeteSelect'));
        $isRequeteSaved       = ($request->request->get('isRequetSaved-itemRequete') === 'true' );

        $res = $this->generationTableau($dateDebut , $dateFin, $modelReferencementId, $contexteId, $isRequeteSaved);
        
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/ItemRequete/tableau.html.twig', array(
            'entetes'   => $res['entetes'],
            'lignes'    => $res['lignes'],
            'resultats' => $res['resultats']
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
        $dateDebut    = $request->request->get('datedebut-itemRequete');
        $dateFin      = $request->request->get('dateFin-itemRequete');

        $modelReferencementId = intval($request->request->get('categorieModeleReferencementItemRequeteSelect'));
        $contexteId           = intval($request->request->get('categorieContexteItemRequeteSelect'));
        $isRequeteSaved       = ($request->request->get('isRequetSaved-itemRequete') === 'true' );

        $res = $this->generationTableau($dateDebut , $dateFin, $modelReferencementId, $contexteId, $isRequeteSaved);

        //Colonnes communes
        $colonnes = array(
            'titre' => 'Modèle de référencement',
        );

        //Récupération des références concernant le choix entre typeES et profil
        foreach ($res["entetes"] as $enteteTableau) 
        {
            $colonnes[$enteteTableau->getId()] = $enteteTableau->getLibelle();
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');
        $datas         = $this->get('hopitalnumerique_stat.manager.statrecherche')->getDatasForExport( $res );

        return $this->get('hopitalnumerique_stat.manager.statrecherche')->exportCsv( $colonnes, $datas, 'export-item-requete.csv', $kernelCharset );
    }

    /* ----  Vue des productions  ---- */

    /**
     * Affiche les statistiques des items de productions
     */
    public function indexProductionAction( )
    {
        $categsContexte       = $this->get('hopitalnumerique_reference.manager.reference')->findByParent($this->get('hopitalnumerique_reference.manager.reference')->findOneById(222));
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/ItemProduction/bloc.html.twig', array(
            'categsContexte'      => $categsContexte
        ));
    }


    /**
     * Génération du tableau à exporter
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * 
     * @return View
     */
    public function generateTableauProductionAction( Request $request )
    {
        //Récupération de la requete
        $dateDebut    = $request->request->get('datedebut-itemProduction');
        $dateFin      = $request->request->get('dateFin-itemProduction');

        $contexteId            = intval($request->request->get('categorieContexteItemProductionSelect'));
        $isRequeteSaved        = ($request->request->get('isRequetSaved-itemProduction') === 'true' );

        $res = $this->generationTableauProduction($dateDebut , $dateFin, $contexteId, $isRequeteSaved);
        
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/ItemProduction/tableau.html.twig', array(
            'entetes'   => $res['entetes'],
            'lignes'    => $res['lignes'],
            'resultats' => $res['resultats']
        ));
    }

    /**
     * Génération du tableau à exporter
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * 
     * @return View
     */
    public function exportCSVProductionAction( Request $request )
    {
        //Récupération de la requete
        $dateDebut    = $request->request->get('datedebut-itemProduction');
        $dateFin      = $request->request->get('dateFin-itemProduction');

        $contexteId           = intval($request->request->get('categorieContexteItemProductionSelect'));
        $isRequeteSaved       = ($request->request->get('isRequetSaved-itemProduction') === 'true' );

        $res = $this->generationTableauProduction($dateDebut , $dateFin, $contexteId, $isRequeteSaved);

        //Colonnes communes
        $colonnes = array(
            'titre' => 'Modèle de référencement',
        );

        //Récupération des références concernant le choix entre typeES et profil
        foreach ($res["entetes"] as $enteteTableau) 
        {
            $colonnes[$enteteTableau->getId()] = $enteteTableau->getLibelle();
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');
        $datas         = $this->get('hopitalnumerique_stat.manager.statrecherche')->getDatasForExport( $res );

        return $this->get('hopitalnumerique_stat.manager.statrecherche')->exportCsv( $colonnes, $datas, 'export-item-requete.csv', $kernelCharset );
    }







    /**
     * Code appelé lors de la génération du tableau et de l'export CSV
     *
     * @return array
     */
    private function generationTableau($dateDebut , $dateFin, $modelReferencementId, $contexteId, $isRequeteSaved)
    {
        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);
        $lignes            = array();

        $modelReferencement = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $modelReferencementId));
        
        $entetes = $this->get('hopitalnumerique_reference.manager.reference')->findByParent($this->get('hopitalnumerique_reference.manager.reference')->findOneById($contexteId));
        
        $modelReferencement           = $this->get('hopitalnumerique_reference.manager.reference')->getArboFromAReference( $modelReferencement )[0];
        $lastChildsModelReferencement = $this->getLastChildRecursive($modelReferencement, $tab = array(), '');

        //Récupère les entités correspondantes aux lignes
        foreach ($lastChildsModelReferencement as $key => $lastChild) 
        {
            $lignes[$key] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $lastChild['id']));
        }

        $resultats = array();

        //Pour compter le nombre de requetes faite par ligne/colonne on va faire nbColonnes * nbLignes reqûete plutot que de récupérer tout et trier ensuite (table de plusieurs millions de lignes à terme)
        foreach ($entetes as $entete)
        {
            foreach ($lignes as $ligne) 
            {
                if(!array_key_exists($entete->getId(), $resultats))
                    $resultats[$entete->getId()] = array();

                $resultats[$entete->getId()][$ligne->getId()] = $this->get('hopitalnumerique_stat.manager.statrecherche')->getStatRechercheByCoupleRef($entete->getId(), $ligne->getId(), $dateDebutDateTime, $dateFinDateTime, $isRequeteSaved);
            }
        }

        return array(
            'entetes'   => $entetes,
            'lignes'    => $lignes,
            'resultats' => $resultats
        );
    }

    /**
     * Code appelé lors de la génération du tableau et de l'export CSV
     *
     * @return array
     */
    private function generationTableauProduction($dateDebut , $dateFin, $contexteId, $isRequeteSaved)
    {
        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);
        $lignes            = array();
        
        $entetes   = $this->get('hopitalnumerique_reference.manager.reference')->findByParent($this->get('hopitalnumerique_reference.manager.reference')->findOneById($contexteId));
        $categoriesProduction    = $this->get('hopitalnumerique_reference.manager.reference')->findByParent($this->get('hopitalnumerique_reference.manager.reference')->findOneById(175));

        foreach ($categoriesProduction as $categ) 
        {
            $lignes[$categ->getLibelle()] = $categ;
        }

        $resultats = array();

        //Pour compter le nombre de requetes faite par ligne/colonne on va faire nbColonnes * nbLignes reqûete plutot que de récupérer tout et trier ensuite (table de plusieurs millions de lignes à terme)
        foreach ($entetes as $entete)
        {
            foreach ($lignes as $ligne) 
            {
                if(!array_key_exists($entete->getId(), $resultats))
                    $resultats[$entete->getId()] = array();

                $resultats[$entete->getId()][$ligne->getId()] = $this->get('hopitalnumerique_stat.manager.statrecherche')->getStatRechercheByCategAndRef($entete->getId(), $ligne->getId(), $dateDebutDateTime, $dateFinDateTime, $isRequeteSaved);
            }
        }

        return array(
            'entetes'   => $entetes,
            'lignes'    => $lignes,
            'resultats' => $resultats
        );
    }

    /**
     * Fonction récursive permettant de récuperer les derniers fils d'une reference
     *
     * @param stdClass $reference Tableau des references à fouiller
     * @param array    $tab       Tableau des derniers fils de la référence
     *
     * @return array
     */
    private function getLastChildRecursive($reference, $tab, $lib)
    {
        $referenceArray = get_object_vars($reference);

        //Transforme le stdClass en array
        $childsArray = $referenceArray["childs"];
        $lib         = ( $lib === "" ) ? $referenceArray['libelle'] : $lib . ' - ' . $referenceArray['libelle'];

        //Si pas de fils alors on est au dernier niveau, on l'ajoute au tableau
        if(empty($childsArray))
        {
            $tab[$lib] = $referenceArray;
        }
        //Sinon on parcourt les fils
        else
        {
            foreach ( $childsArray as $childs) 
            {
                $tab = $this->getLastChildRecursive($childs, $tab, $lib);
            }
        }

        return $tab;
    }
}
