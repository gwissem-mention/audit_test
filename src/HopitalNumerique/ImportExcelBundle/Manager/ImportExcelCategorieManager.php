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
            //Création d'une nouvelle catégorie
            $categorie = $this->createEmpty();

            $categorie->setTitle($categorieDonnees['libelle']);
            $categorie->setNote($categorieDonnees['note']);
            $categorie->setOutil($outil);

            $this->save( $categorie );
        }
    }
}