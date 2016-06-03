<?php

namespace HopitalNumerique\RechercheParcoursBundle\Manager;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class MaitriseUserManager extends BaseManager
{
    protected $class = 'HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser';

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
     * Récupère les notes pour l'export
     *
     * @return array
     */
    public function getDatasForExportByEtape( $donneesTab )
    {
        $results = array();

        foreach($donneesTab["etapes"] as $etape) 
        {
            $row = array();

            //simple stuff
            $row['df']           = $etape->getRechercheParcours()->getReference()->getLibelle();
            $row['id']           = $etape->getId();
            $row['etape']        = $etape->getReference()->getLibelle();

            //Parcours les colonnes du filtre typeES ou profil
            foreach ($donneesTab["entetesTableau"] as $key => $enteteTableau) 
            {
                $row[$key] = '';
                //Si il y a des notes pour ce point dur et ce filtre on l'affiche/les affiche
                if(array_key_exists($etape->getId(), $donneesTab["notesMoyenneParEtape"])
                    && array_key_exists($key, $donneesTab["notesMoyenneParEtape"][$etape->getId()]))
                {
                    $row[$key] = $donneesTab["notesMoyenneParEtape"][$etape->getId()][$key]['value'] . ' (NbNote : ' . $donneesTab["notesMoyenneParEtape"][$etape->getId()][$key]['nbNote'] . ', NbUser : ' . $donneesTab["notesMoyenneParEtape"][$etape->getId()][$key]['nbUser'] .')'  ;
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
     * Retourne la moyenne des notes pour les étapes passées en param
     *
     * @return array Tableau des moyenne triées par étape
     */
    public function getAverageAllEtapesAllUser( $profilType)
    {
        //Récupération du tableau des objets maitrisés
        $objetsMaitrises = $this->getRepository()->getAverageAllEtapesAllUser( $profilType )->getQuery()->getResult();
        
        $moyennes = array();
        //Cast des moyenne en int arrondi à l'entier
        foreach ($objetsMaitrises as $key => $etape) 
        {
            $filtre                               = $etape['filtreId'] == NULL ? 'NC' : $etape['filtreId'];
            $moyennes[$etape['etapeId']][$filtre] = array(
                'value'   => intval($etape['moyenne'], 0),
                'nbNote'  => intval($etape['nbNote'], 0),
                'nbUser'  => intval($etape['nbUser'], 0)
            );
        }

        return $moyennes;
    }

    public function removeNotesNotInEntities($notes, $entitiesPropertiesKeyedByGroup)
    {
        $objetIds = [];

        foreach ($entitiesPropertiesKeyedByGroup as $entitiesProperties) {
            foreach ($entitiesProperties as $entityProperties) {
                if (Entity::ENTITY_TYPE_OBJET == $entityProperties['entityType']) {
                    $objetIds[] = $entityProperties['entityId'];
                }
            }
        }

        foreach ($notes as $entityId => $note) {
            if (!in_array($entityId, $objetIds)) {
                $this->delete($note);
                unset($notes[$entityId]);
            }
        }

        return $notes;
    }
}
