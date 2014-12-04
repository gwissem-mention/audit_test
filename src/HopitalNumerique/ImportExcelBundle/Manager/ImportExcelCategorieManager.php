<?php

namespace HopitalNumerique\ImportExcelBundle\Manager;

use HopitalNumerique\AutodiagBundle\Manager\CategorieManager as CategManagerAutodiag;

/**
 * Manager de l'entité Categorie.
 */
class ImportExcelCategorieManager extends CategManagerAutodiag
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Categorie';

    /**
     * Récupération des données des catégories du fichier excel d'import
     *
     * @param Array $arrayCategories Tableau des données de catégories à persist
     *
     * @return void
     */
    public function saveCategImported( $arrayCategories, $outil )
    {
        foreach ($arrayCategories as $categorieDonnees) 
        {
            $categorie = null;

            //Si il y a un id on vérifie qu'il correspond à un champ en base pour de l'édition, sinon l'ajoute
            if(!is_null($categorieDonnees['id']))
            {
                $categorie = $this->findOneBy( array('id' => $categorieDonnees['id']) );
            }

            //Création d'une nouvelle catégorie
            if(is_null($categorie))
            {
                $categorie = $this->createEmpty();
            }

            $categorie->setTitle($categorieDonnees['libelle']);
            $categorie->setNote($categorieDonnees['note']);
            $categorie->setOutil($outil);

            $this->save( $categorie );
        }
    }
}