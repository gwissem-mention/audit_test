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
    protected $_securityContext;

    /**
     * @param EntityManager    $em               [description]
     * @param ManagerReference $managerReference [description]
     * @param SecurityContext $securityContext Security Context
     */
    public function __construct($em, $managerReference, $securityContext)
    {
        parent::__construct($em);
        $this->_managerReference = $managerReference;
        $this->_securityContext = $securityContext;
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
    public function getStatRechercheByCoupleRef( $ref1, $ref2, $dateDebutDateTime, $dateFinDateTime, $isRequeteSaved )
    {   
        $compteur = 0;
        $statsRecherche = $this->getRepository()->getStatRechercheByDateAndRequeteSaved($dateDebutDateTime, $dateFinDateTime, $isRequeteSaved)->getQuery()->getResult();

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
     * Compte le nombre de requetes faites pour un couple de ref donnée
     *
     * @param int $ref   Identifiant d'une référence
     * @param int $categ Identifiant de la catégorie
     * @param DateTime or null $dateDebutDateTime DateTime de la date de début de la recherche si elle est renseignée, sinon null
     * @param DateTime or null $dateFinDateTime   DateTime de la date de fin de la recherche si elle est renseignée, sinon null
     *
     * @return integer
     */
    public function getStatRechercheByCategAndRef( $ref, $categ, $dateDebutDateTime, $dateFinDateTime, $isRequeteSaved )
    {   
        $compteur = 0;
        $statsRecherche = $this->getRepository()->getStatRechercheByDateAndRequeteSaved($dateDebutDateTime, $dateFinDateTime, $isRequeteSaved)->getQuery()->getResult();

        foreach ($statsRecherche as $statRecherche) 
        {
            $refIdByStat = array();
            foreach ($statRecherche->getReferences() as $reference) 
            {
                $refIdByStat[] = $reference->getId();
            }

            if(in_array($ref, $refIdByStat))
            {
                $categsId = explode(',', $statRecherche->getCategPointDur());
                if(in_array($categ, $categsId))
                {
                    $compteur++;
                }
            }
        }

        return $compteur;
    }

    /**
     * Retourne les stats fantomes (nbRes = 0)
     *
     * @param DateTime or null $dateDebutDateTime DateTime de la date de début de la recherche si elle est renseignée, sinon null
     * @param DateTime or null $dateFinDateTime   DateTime de la date de fin de la recherche si elle est renseignée, sinon null
     *
     * @return array(StatRecherche)
     */
    public function getStatFantome( $dateDebutDateTime, $dateFinDateTime )
    { 
        return $this->getRepository()->getStatFantome($dateDebutDateTime, $dateFinDateTime)->getQuery()->getResult();
    }

    /**
     * Sauvegarde l'ensemble de la requete affichée en base pour les stats
     *
     * @param array $tableauIdRef Tableau formaté par la requete de récupération des références dans la recherche
     *
     * @return Void
     */
    public function sauvegardeRequete(array $tableauIdRef, $user, $categ, $nbResultats, $isRequete)
    {
        if(!is_null($user) && $this->_securityContext->isGranted('ROLE_ADMINISTRATEUR_1'))
        {
            return;
        }

        $referencesJSON = json_encode($tableauIdRef);

        //Récupération des références correspondant à la requête
        $references = $this->getTabReferenceByArrayId($tableauIdRef);

        $statRecherche = $this->createEmpty();

        if(!is_null($user) && "anon." !== $user)
            $statRecherche->setUser($user);

        $statRecherche->setReferences($references);
        $statRecherche->setDate(new \DateTime());
        $statRecherche->setNbResultats($nbResultats);
        $statRecherche->setRequete($referencesJSON);
        $statRecherche->setIsRequeteSaved($isRequete);
        $statRecherche->setCategPointDur($categ);

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

        foreach($donneesTab["lignes"] as $key => $ligne) 
        {
            $row = array();

            //simple stuff
            $row['titre'] = $key;

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
     * Fonction qui retourne les différentes statistiques pour les différents forum
     * 
     * @return QueryBuilder
     */
    public function getStatsForum($dateDebut, $dateFin)
    {   
        $stats = $this->getRepository()->getStatsForum( $dateDebut, $dateFin )->getQuery()->getResult();

        $results = array();
        foreach ($stats as $stat) 
        {
            if( !array_key_exists($stat["forumId"], $results) )
            {
                $results[ $stat["forumId"] ] = array(
                    $stat["boardId"] => array(
                        "topics" => $stat['topicId'] == null ? array() : array(
                            $stat["topicId"] => array(
                                "nbVues" => $stat['nbVues'],
                                "nbPosts" => 0,
                                "name" => $stat['topicName']
                            )
                        ),
                        "nbTopics" => 0,
                        "name" => $stat['boardName']
                    )
                );
            }
            elseif( !array_key_exists($stat["boardId"], $results[ $stat["forumId"] ]) )
            {
                $results[ $stat["forumId"] ][ $stat["boardId"] ] = array(
                    "topics" => $stat['topicId'] == null ? array() : array(
                        $stat["topicId"] => array(
                            "nbVues" => $stat['nbVues'],
                            "nbPosts" => 0,
                            "name" => $stat['topicName']
                        )
                    ),
                    "nbTopics" => 0,
                    "name" => $stat['boardName']
                );
            }
            elseif( !array_key_exists($stat["topicId"], $results[ $stat["forumId"] ][ $stat["boardId"] ]['topics']) )
            {
                $results[ $stat["forumId"] ][ $stat["boardId"] ]['topics'][ $stat["topicId"] ] = array(
                    "nbVues" => $stat['nbVues'],
                    "nbPosts" => 0,
                    "name" => $stat['topicName']
                );
            }
            
            if( $stat['topicId'] != null )
            {
                $results[ $stat["forumId"] ][ $stat["boardId"] ]["nbTopics"]++;
                $results[ $stat["forumId"] ][ $stat["boardId"] ]["topics"][ $stat["topicId"] ]["nbPosts"]++;
            }
        }
        
        // tri + calcul de la somme des vues
        foreach( $results as &$forum )
        {
            foreach($forum as &$board)
            {   
                if( !array_key_exists("nbVuesTotal", $board) )
                {
                    $board['nbVuesTotal'] = 0;
                }
                foreach( $board['topics'] as &$topic )
                {
                    $board['nbVuesTotal'] += $topic["nbVues"];
                }
                ksort($board);
            }
            ksort($forum);
        }
        ksort($results);

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