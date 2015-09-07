<?php

namespace HopitalNumerique\ImportExcelBundle\Manager;

use HopitalNumerique\AutodiagBundle\Manager\ChapitreManager as ChapitreManagerAutodiag;
use Nodevo\ToolsBundle\Tools\Chaine;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Manager\ResultatManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Manager de l'entité Categorie.
 */
class ImportExcelChapitreManager extends ChapitreManagerAutodiag
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Chapitre';


    /**
     * Constructeur du manager gérant les chapitres d'outil.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @param \HopitalNumerique\AutodiagBundle\Manager\ResultatManager $resultatManager Le manager de l'entité Resultat
     * @return void
     */
    public function __construct(EntityManager $entityManager, ResultatManager $resultatManager, UserManager $userManager, ReferenceManager $referenceManager)
    {
        parent::__construct($entityManager, $resultatManager, $userManager, $referenceManager);
    }

    /**
     * Récupération des données des catégories du fichier excel d'import
     *
     * @param Array $arrayChapitres Tableau des données de catégories à persist
     *
     * @return void
     */
    public function saveChapitreImported( $arrayChapitres, $outil )
    {
        $arrayIdsChapitres = array();
        $arrayIdsChapitresImported = array();
        $arrayIdsChapitresSaved    = array();

        //Récupération des id de questions présent dans le fichier d'import
        foreach ($arrayChapitres as $chapitre) 
        {
            if(!is_null($chapitre['id']))
            {
                $arrayIdsChapitresImported[] = $chapitre['id'];
            }
        }
        
        foreach ($arrayChapitres as $key => $chapitreDonnees) 
        {
            $chapitre = null;

            //Si il y a un id on vérifie qu'il correspond à un champ en base pour de l'édition, sinon l'ajoute
            if(!is_null($chapitreDonnees['id']))
            {
                $chapitre = $this->findOneBy(array('id' => $chapitreDonnees['id']));

            }
            if(is_null($chapitre))
            {
                //Création d'une nouvelle chapitre
                $chapitre = $this->createEmpty();
                $chapitre->setOrder($key + 1);
                if(array_key_exists('id', $chapitreDonnees) && !is_null($chapitreDonnees['id']))
                {
                    $chapitre->setId($chapitreDonnees['id']);
                }
            }
            elseif($chapitre->getOutil()->getId() !== $outil->getId())
            {
                die('Erreur dans les chapitres : Vous modifié un chapitre d\'un autre autodiagnostic !');
            }

            $tool  = new Chaine( $chapitreDonnees['libelle'] );
            $chapitre->setAlias( $tool->minifie() );

            $chapitre->setTitle($chapitreDonnees['libelle']);

            //$chapitre->setNote($chapitreDonnees['note']);
            $chapitre->setCode($chapitreDonnees['code']);
            $chapitre->setNoteOptimale($chapitreDonnees['noteOptimale']);
            $chapitre->setNoteMinimale($chapitreDonnees['noteMinimale']);
            $chapitre->setSynthese($chapitreDonnees['synthese']);
            $chapitre->setIntro($chapitreDonnees['introduction']);
            $chapitre->setDesc($chapitreDonnees['description']);
            $chapitre->setLien($chapitreDonnees['lien']);
            $chapitre->setDescriptionLien($chapitreDonnees['descriptionLien']);
            $chapitre->setAffichageRestitutionBarre($chapitreDonnees['affichageRestitutionBarre']);
            $chapitre->setAffichageRestitutionRadar($chapitreDonnees['affichageRestitutionRadar']);

            if (trim($chapitreDonnees['idParent']) !== '' && isset($arrayIdsChapitres[$chapitreDonnees['idParent']]))
            {
                $chapitreParent = $this->findOneBy(array('id' => $arrayIdsChapitres[$chapitreDonnees['idParent']], 'outil' => $outil));
                $chapitre->setParent($chapitreParent);
            }
            elseif (trim($chapitreDonnees['codeParent']) !== '' )
            {
                $chapitreParent = $this->findOneBy(array('code' => $chapitreDonnees['codeParent'], 'outil' => $outil));
                $chapitre->setParent($chapitreParent);
            }

            $chapitre->setOutil($outil);

            $this->saveForceId( $chapitre );

            $arrayIdsChapitres[('' != $chapitreDonnees['id'] ? $chapitreDonnees['id'] : $chapitre->getId())] = $chapitre->getId();

            $arrayIdsChapitresSaved[] = $chapitre->getId();
        }

        //Récupération des chapitres à l'autodiag
        $chapitres = $this->findBy(array('outil' => $outil));
        $chapitresToDelete = array();
        foreach ($chapitres as $chapitre) 
        {
            //Si le chapitre n'est pas dans les lignes importées et ne vient pas d'etre importé on la delete
            if(!in_array($chapitre->getId(), $arrayIdsChapitresImported)
                && !in_array($chapitre->getId(), $arrayIdsChapitresSaved))
            {
                $chapitresToDelete[] = $chapitre;
            }
        }
        
        $this->delete($chapitresToDelete);
        
        return $arrayIdsChapitres;
    }
}
