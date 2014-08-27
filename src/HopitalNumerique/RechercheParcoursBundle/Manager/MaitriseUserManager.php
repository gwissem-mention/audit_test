<?php

namespace HopitalNumerique\RechercheParcoursBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class MaitriseUserManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser';

    /**
     * Met à jour l'ordre du détails
     *
     * @param User $user L'utilisateur auquel appartient les notes
     *
     * @return array(HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser)
     */
    public function getAllOrderedByPointDurForParcoursEtape( $user, $idParcoursEtape )
    {
        $notesByPointDur = array();
        $notes = $this->findBy(array('user' => $user, 'rechercheParcoursDetails' => $idParcoursEtape));

        foreach ($notes as $note)
        {
            $notesByPointDur[$note->getObjet()->getId()] = $note;
        }

        return $notesByPointDur;
    }

    public function getAllNotesSortByObjet( $filtre, $dateDebut, $dateFin )
    {
        $notes     = $this->findAll();
        $notesTemp = array();

        //Trie de l'ensemble des notes par points dur
        foreach ($notes as $note) 
        {
            if(!is_null($dateDebut) && $note->getDateMaitrise() <= $dateDebut)
            {
                continue;
            }
            if(!is_null($dateFin) && $note->getDateMaitrise() >= $dateFin)
            {
                continue;
            }

            //1er niveau : Récupération de l'id de l'objet courant
            $objCourantId = $note->getObjet()->getId();
            
            //2eme niveau : filtre sur l'utilisateur
            $userCourant  = $note->getUser();
            //Type d'établissement
            if(!is_null($userCourant->getStatutEtablissementSante()))
               $referencesTemp[] =  $userCourant->getStatutEtablissementSante();

            //Métier internaute
            if(!is_null($userCourant->getProfilEtablissementSante()))
               $referencesTemp[] =  $userCourant->getProfilEtablissementSante();

            //Récupération de la valeur en fonction du filtre passé en param
            switch ($filtre) {
                case 'typeES':
                    $referenceUser = is_null($userCourant->getStatutEtablissementSante()) ? 'NC' : $userCourant->getStatutEtablissementSante()->getId();
                    break;
                case 'profil':
                    $referenceUser = is_null($userCourant->getProfilEtablissementSante()) ? 'NC' : $userCourant->getProfilEtablissementSante()->getId();
                    break;
                default:
                    $referenceUser = 'NC';
                    break;
            }

            //3eme niveau : catégorie de la note
            //Non concerné sauf si "getNonConcerne" retourne false
            $filtreNote = 'NC';
            if(!$note->getNonConcerne())
            {
                if($note->getPourcentageMaitrise() == 0)
                    $filtreNote = '0';
                elseif($note->getPourcentageMaitrise() <= 20)
                    $filtreNote = '1-20%';
                elseif($note->getPourcentageMaitrise() <= 40)
                    $filtreNote = '21-40%';
                elseif($note->getPourcentageMaitrise() <= 60)
                    $filtreNote = '41-60%';
                elseif($note->getPourcentageMaitrise() <= 80)
                    $filtreNote = '61-80%';
                else
                    $filtreNote = '81-100%';
            }

            //Création du premier niveau : l'Objet
            if(!array_key_exists($objCourantId, $notesTemp))
                $notesTemp[$objCourantId] = array();

            //Création du second niveau : le filtre sur l'user
            if(!array_key_exists($referenceUser, $notesTemp[$objCourantId]))
                $notesTemp[$objCourantId][$referenceUser] = array();

            //Création du troisième niveau : la catégorie de la note
            if(!array_key_exists($filtreNote, $notesTemp[$objCourantId][$referenceUser]))
                $notesTemp[$objCourantId][$referenceUser][$filtreNote] = 0;

            $notesTemp[$objCourantId][$referenceUser][$filtreNote]++;
        }

        return $notesTemp;
    }

    /**
     * Récupère les notes pour l'export
     *
     * @return array
     */
    public function getDatasForExport( $donneesTab )
    {
        $results = array();

        foreach($donneesTab["pointsDur"] as $pointDur) 
        {
            $row = array();

            //simple stuff
            $row['id']           = $pointDur["id"];
            $row['titre']        = $pointDur["titre"];

            //Parcours les colonnes du filtre typeES ou profil
            foreach ($donneesTab["entetesTableau"] as $key => $enteteTableau) 
            {
                $row[$key] = '';
                //Si il y a des notes pour ce point dur et ce filtre on l'affiche/les affiche
                if(array_key_exists($pointDur["id"], $donneesTab["notes"])
                    && array_key_exists($key, $donneesTab["notes"][$pointDur["id"]]))
                {
                    foreach ($donneesTab["notes"][$pointDur["id"]][$key] as $categ => $nbNote) 
                    {
                        $row[$key] .= $categ . ':' . $nbNote . '; ';
                    }
                }
            }
            //add row To Results
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Retourne la moyenne des notes pour les étapes passées en param
     *
     * @param RechercheParcours $rechercheParcours
     *
     * @return array Tableau des moyenne triées par étape
     */
    public function getAverage( $rechercheParcours, $user )
    {
        $etapesId = $rechercheParcours->getRecherchesParcoursDetailsIds();

        //Récupération du tableau des objets maitrisés
        $objetsMaitrises = $this->getRepository()->getAverage( $etapesId, $user )->getQuery()->getResult();

        $moyennes = array();
        //Cast des moyenne en int arrondi à l'entier
        foreach ($objetsMaitrises as $key => $etape) 
        {
            $moyennes[$etape['etapeId']] = intval($etape['moyenne'], 0);
        }

        return $moyennes;
    }

    /**
     * Supprime les notes des objets qui ne sont plus d'actualités
     *
     * @param [type] $notes  Ensemble des notes à trier
     * @param [type] $objets Objets à garder
     *
     * @return array(MaitriseUser) Ensemble des notes triées
     */
    public function cleanNotesByObjet($notes, $objets)
    {
        //Tableau temp des notes
        $notesTemp = array();
        $objetIds  = array();

        //Récupération de l'ensemble des Ids des objets pour le tri
        foreach ($objets as $objet) 
        {
            if($objet['categ'] === 'point-dur' && $objet['primary'])
                $objetIds[] = $objet['id'];
        }

        //Tri des notes, si la note ne correspond plus à un objet à afficher on la supprime
        foreach ($notes as $key => $note) 
        {
            if(!in_array($key, $objetIds) )
            {
                $this->delete($note);
            }
            else
            {
                $notesTemp[$note->getObjet()->getId()] = $note;
            }
        }

        return $notesTemp;
    }
}