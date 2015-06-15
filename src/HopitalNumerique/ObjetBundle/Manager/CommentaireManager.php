<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Commentaire.
 */
class CommentaireManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ObjetBundle\Entity\Commentaire';



    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $commentaires = array();
        
        $results = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
        
        foreach ($results as $key => $result)
        {
            $commentaires[ $result['id'] ] = $result;
            
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
            unset($commentaires[$result['id']]['userNom']);
            unset($commentaires[$result['id']]['userPrenom']);
            
            //Ajout de la colonne "Prenom NOM"
            $commentaires[ $result['id'] ]['nomPrenom'] = $prenom.' '.$nom;
        }
        
        return array_values($commentaires);
    }


    
    /**
     * Passe l'ensemble des commentaires à publier
     *
     * @param array     $commentaires Liste des commentaires
     *
     * @return empty
     */
    public function publierEtatCommentaire( $commentaires )
    {
        foreach($commentaires as $commentaire) {
            $commentaire->setPublier( true );
            $this->_em->persist( $commentaire );
        }
    
        //save
        $this->_em->flush();
    }


    
    /**
     * Passe l'ensemble des commentaires à dépublier
     *
     * @param array     $commentaires Liste des commentaires
     *
     * @return empty
     */
    public function depublierEtatCommentaire( $commentaires )
    {
        foreach($commentaires as $commentaire) {
            $commentaire->setPublier( false );
            $this->_em->persist( $commentaire );
        }
    
        //save
        $this->_em->flush();
    }

    public function findCommentaireByDomaine( $idDomaine )
    {
        return $this->getRepository()->findCommentaireByDomaine($idDomaine)->getQuery()->getResult();
    }

}