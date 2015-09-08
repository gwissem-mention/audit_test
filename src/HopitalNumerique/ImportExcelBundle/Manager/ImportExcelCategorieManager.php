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
        $arrayIdsCategoriesImported = array();
        $arrayIdsCategoriesSaved    = array();

        //Récupération des id de questions présent dans le fichier d'import
        foreach ($arrayCategories as $categorie) 
        {
            if(!is_null($categorie['id']))
            {
                $arrayIdsCategoriesImported[] = $categorie['id'];
            }
        }

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
            elseif($categorie->getOutil()->getId() !== $outil->getId())
            {
                die('Erreur dans les catégories : Vous modifié une catégorie d\'un autre autodiagnostic !');
            }

            $categorie->setTitle($categorieDonnees['libelle']);
            $categorie->setNote($categorieDonnees['note']);
            $categorie->setOutil($outil);

            $this->save( $categorie );

            $arrayIdsCategoriesSaved[] = $categorie->getId();
        }

        //Récupération des categories à l'autodiag
        $categories = $this->findBy(array('outil' => $outil));
        $categoriesToDelete = array();
        foreach ($categories as $categorie) 
        {
            //Si la categorie n'est pas dans les lignes importées et ne vient pas d'etre importé on la delete
            if(!in_array($categorie->getId(), $arrayIdsCategoriesImported)
                && !in_array($categorie->getId(), $arrayIdsCategoriesSaved))
            {
                $categoriesToDelete[] = $categorie;
            }
        }
        
        $this->delete($categoriesToDelete);
    }
}