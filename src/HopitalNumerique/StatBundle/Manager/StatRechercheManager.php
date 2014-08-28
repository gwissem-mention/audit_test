<?php

namespace HopitalNumerique\StatBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class StatRechercheManager extends BaseManager
{
    protected $_managerReference;
    protected $_class = 'HopitalNumerique\StatBundle\Entity\StatRecherche';

    /**
     * @param EntityManager    $em               [description]
     * @param ManagerReference $managerReference [description]
     */
    public function __construct($em, $managerReference)
    {
        parent::__construct($em);
        $this->_managerReference = $managerReference;
    }

    /**
     * Compte le nombre de requetes faites pour un couple de ref donnée
     *
     * @param int $ref1 Identifiant de la référence n°1 du couple
     * @param int $ref2 Identifiant de la référence n°2 du couple
     * @param DateTime or null $dateDebutDateTime DateTime de la date de début de la recherche si elle est renseignée, sinon null
     * @param DateTime or null $dateFinDateTime   DateTime de la date de fin de la recherche si elle est renseignée, sinon null
     *
     * @return integer
     */
    public function getStatRechercheByCoupleRef( $ref1, $ref2, $dateDebutDateTime, $dateFinDateTime )
    {   
        $compteur = 0;
        $statsRecherche = $this->getRepository()->getStatRechercheByCoupleRef($ref1, $ref2, $dateDebutDateTime, $dateFinDateTime)->getQuery()->getResult();

        foreach ($statsRecherche as $statRecherche) 
        {
            $refIdByStat = array();
            foreach ($statRecherche->getReferences() as $reference) 
            {
                $refIdByStat[] = $reference->getId();
            }

            if(in_array($ref1, $refIdByStat) && in_array($ref2, $refIdByStat))
                $compteur++;
        }

        return $compteur;
    }

    /**
     * Sauvegarde l'ensemble de la requete affichée en base pour les stats
     *
     * @param array $tableauIdRef Tableau formaté par la requete de récupération des références dans la recherche
     *
     * @return Void
     */
    public function sauvegardeRequete(array $tableauIdRef, $user)
    {
        //Récupération des références correspondant à la requête
        $references = $this->getTabReferenceByArrayId($tableauIdRef);

        $statRecherche = $this->createEmpty();

        if(!is_null($user) && "anon." !== $user)
            $statRecherche->setUser($user);

        $statRecherche->setReferences($references);
        $statRecherche->setDate(new \DateTime());

        $this->save($statRecherche);
    }

    /**
     * Récupère les notes pour l'export
     *
     * @return array
     */
    public function getDatasForExport( $donneesTab )
    {
        $results = array();

        $donnees = $donneesTab['resultats'];

        foreach($donneesTab["lignes"] as $ligne) 
        {
            $row = array();

            //simple stuff
            $row['titre'] = $ligne->getLibelle();

            //Parcours les colonnes du filtre typeES ou profil
            foreach ($donneesTab["entetes"] as $enteteTableau) 
            {
                $row[$enteteTableau->getId()] = '';
                //Si il y a des notes pour ce point dur et ce filtre on l'affiche/les affiche
                if(array_key_exists($enteteTableau->getId(), $donnees)
                    && array_key_exists($ligne->getId(), $donnees[$enteteTableau->getId()] ))
                {
                    $row[$enteteTableau->getId()] = $donnees[$enteteTableau->getId()][$ligne->getId()];
                }
            }
            //add row To Results
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Retourne un tableau d'entité référence en fonction du tableau d'id passés en param : très gourmant en nombre de requete => à OPTIMISER
     *
     * @param array $tableauIdRef Tableau formaté par la requete de récupération des références dans la recherche
     *
     * @return array[Reference]
     */
    private function getTabReferenceByArrayId(array $tableauIdRef)
    {
        //Tableau de retour de l'ensemble des références de la recherche
        $references = array();
        foreach ($tableauIdRef as $categ => $tableauRefByCateg) 
        {
            foreach ($tableauRefByCateg as $ref) 
            {
                //Ajout de la référence
                $referenceTemp = $this->_managerReference->findOneBy(array('id' => $ref));
                $references[ $referenceTemp->getId() ] = $referenceTemp;
                
                //parcourt tout les parents pour avoir l'ensemble des références utilisées
                while( !is_null( $referenceTemp->getParent() ) )
                {
                    $referenceTemp = $referenceTemp->getParent();
                    //Si ce parent n'a pas déjà été ajouté
                    if( !array_key_exists($referenceTemp->getId(), $references) )
                        $references[ $referenceTemp->getId() ] = $referenceTemp;
                }
            }
        }

        return $references;
    }
}