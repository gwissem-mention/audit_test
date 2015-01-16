<?php

namespace HopitalNumerique\ModuleBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Inscription.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ModuleBundle\Entity\Inscription';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $inscriptions = array();
        
        $results = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
        
        foreach ($results as $key => $result)
        {
            $inscriptions[ $result['id'] ] = $result;
            
            // ----Traitement pour transformer le prénom "Jean-luc robert" en "Jean-Luc Robert"
            //Récupération du prénom
            $prenom = strtolower($result['userPrenom']);
            //Découpage du prénom sur le tiret
            $tempsPrenom = explode('-', $prenom);
            //Unsset de la variable
            $prenom = "";
            //Pour chaque bout on met une MAJ sur la première lettre de chaque mot, si il y en plusieurs c'est qu'il y avait un -
            foreach ($tempsPrenom as $key => $tempPrenom)
            {
                $prenom .= ("" !== $prenom) ? ('-' . ucwords($tempPrenom)) : ucwords($tempPrenom);
            }
            
            // ----Mise en majuscule du nom
            $nom = strtoupper($result['userNom']);

            //Suppression du nom et prenom
            unset($inscriptions[$result['id']]['userNom']);
            unset($inscriptions[$result['id']]['userPrenom']);
            
            //Ajout de la colonne "Prenom NOM"
            $inscriptions[ $result['id'] ]['nomPrenom'] = $prenom.' '.$nom;
        }
        
        return array_values($inscriptions);
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getAllDatasForGrid( $condition = null )
    {
        $inscriptions = $this->getRepository()->getAllDatasForGrid( $condition )->getQuery()->getResult();

        $result = array();

        foreach ($inscriptions as $key => $inscription) 
        {
            $nomPrenom = $inscription->getUser()->getAppellation();

            $nbInscritsAccepte   = 0;
            $nbInscritsEnAttente = 0;
            $nbPlacesRestantes   = $inscription->getSession()->getNombrePlaceDisponible();

            foreach ($inscription->getSession()->getInscriptions() as $inscriptionDeLaSession) 
            {
                if($inscriptionDeLaSession->getEtatInscription()->getId() === 406)
                    $nbInscritsEnAttente++;
                elseif($inscriptionDeLaSession->getEtatInscription()->getId() === 407)
                {
                    $nbInscritsAccepte++;
                    $nbPlacesRestantes--;
                }
            }

            $result[$key] = array(
                'id'                  => $inscription->getId(),
                'userId'              => $inscription->getUser()->getId(),
                'sessionId'           => $inscription->getSession()->getId(),
                'moduleTitre'         => $inscription->getSession()->getModule()->getTitre(),
                'dateSession'         => $inscription->getSession()->getDateSession(),
                'nomPrenom'           => $nomPrenom,
                'userRegion'          => ( !is_null( $inscription->getUser()->getRegion() ) ) ? $inscription->getUser()->getRegion()->getLibelle() : '',
                'userProfil'          => ( !is_null( $inscription->getUser()->getProfilEtablissementSante() ) ) ? $inscription->getUser()->getProfilEtablissementSante()->getLibelle() : '',
                'roles'               => $inscription->getUser()->getRoles(),
                'commentaire'         => $inscription->getCommentaire(),
                'etatInscription'     => $inscription->getEtatInscription()->getLibelle(),
                'nbInscrits'          => $nbInscritsAccepte,
                'nbInscritsEnAttente' => $nbInscritsEnAttente,
                'placeRestantes'      => $nbPlacesRestantes . '/' . $inscription->getSession()->getNombrePlaceDisponible(),
                // 'etatParticipation' => $inscription->getEtatParticipation()->getLibelle(),
                // 'etatEvaluation'    => $inscription->getEtatEvaluation()->getLibelle(),
            );
        }

        return $result;
    }
    
    /**
     * Modifie l'état de toutes les inscriptions
     *
     * @param array     $inscriptions Liste des inscriptions
     * @param Reference $ref          RefStatut à mettre
     *
     * @return empty
     */
    public function toogleEtatInscription( $inscriptions, $ref )
    {
        foreach($inscriptions as $inscription) {
            $inscription->setEtatInscription( $ref );
            $this->_em->persist( $inscription );
        }
    
        //save
        $this->_em->flush();
    }
    
    /**
     * Modifie l'état de toutes les participations
     *
     * @param array     $inscriptions Liste des inscriptions
     * @param Reference $ref          RefStatut à mettre
     *
     * @return empty
     */
    public function toogleEtatParticipation( $inscriptions, $ref )
    {
        foreach($inscriptions as $inscription) {
            $inscription->setEtatParticipation( $ref );
            $this->_em->persist( $inscription );
        }
    
        //save
        $this->_em->flush();
    }
    
    /**
     * Modifie l'état de toutes les évaluations
     *
     * @param array     $inscriptions Liste des inscriptions
     * @param Reference $ref          RefStatut à mettre
     *
     * @return empty
     */
    public function toogleEtatEvaluation( $inscriptions, $ref )
    {
        foreach($inscriptions as $inscription) 
        {
            $inscription->setEtatEvaluation( $ref );
            $this->_em->persist( $inscription );
        }
    
        //save
        $this->_em->flush();
    }

    /**
     * Retourne la liste des inscriptions de l'utilisateur pour la création des factures
     *
     * @param User $user L'utilisateur concerné
     *
     * @return array
     */
    public function getForFactures( $user = null )
    {
        return $this->getRepository()->getForFactures( $user )->getQuery()->getResult();
    }

    /**
     * Retourne un boolean pour dire si les inscriptions sont ok
     *
     * @param User $user L'utilisateur concerné
     *
     * @return array
     */
    public function allInscriptionsIsOk( $user )
    {
        //Requete
        $inscriptions = $this->findBy(array('user' => $user, 'etatParticipation' => 411));

        //Parcours des résultats
        foreach ($inscriptions as $inscription) 
        {
            //Il faut que TOUTES les inscriptions de l'utilisateur soient "A participé" et "Évaluée" 
            if($inscription->getEtatParticipation()->getId() !== 411
                || $inscription->getEtatEvaluation()->getId() !== 29)
            {
                //Inscriptions non conforme
                return false;
            }
        }

        return true;
    }

    /**
     * Retourne la liste des inscriptions de l'utilisateur
     *
     * @param User $user L'utilisateur concerné
     * 
     * @return array
     */
    public function getInscriptionsForUser( $user )
    {
        return $this->getRepository()->getInscriptionsForUser( $user )->getQuery()->getResult();
    }
    
    /**
     * Créer un tableau formaté pour l'export CSV
     * 
     * @param type $modules liste des modules
     * @param type $users   liste des utilisateurs
     * 
     * @return type
     */
    public function buildForExport($modules, $users, $primaryKeys){
        
        $colonnes = array(
            "nom"    => "Nom",
            "prenom" => "Prénom"
        );
        foreach($modules as $module){
            $colonnes["module" . $module->getId()] = $module->getTitre();
        }
        
        $inscriptions = $this->getRepository()->getInscriptionsByUser( $primaryKeys )->getQuery()->getResult();
        $donnees = array();
        foreach($inscriptions as $inscription){
            $donnees[ $inscription['userId'] ][ $inscription['moduleId'] ] = date_format($inscription['date'], 'd/m/Y');
        }
        
        $datas = array();
        foreach($users as $user){
            $row = array();
            
            $row['nom']    = $user->getNom();
            $row['prenom'] = $user->getPrenom();
            foreach($modules as $module){
                $row["module" . $module->getId()] = isset($donnees[ $user->getId() ][ $module->getId() ]) 
                        ? $donnees[ $user->getId() ][ $module->getId() ] : "";
            }
            
            $datas[] = $row;
        }
        
        return array('colonnes' => $colonnes, 'datas' => $datas );
    }
}