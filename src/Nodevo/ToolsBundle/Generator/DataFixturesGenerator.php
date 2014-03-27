<?php

namespace Nodevo\ToolsBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator as Generator;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory;

class DataFixturesGenerator extends Generator
{
	protected $filesystem;

	/**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate($bundle, $entities, AbstractClassMetadataFactory $metadataFactory, $erase)
    {
    	$dir = $bundle->getPath();

    	$generatedFixtures = array();

    	foreach ($entities as $entity) {
    		$doctrineEntity = $entity['entity'];
    		$shortName 		= $entity['shortName'];

    		// Fichier à créer
	        $target = sprintf(
	            '%s/DataFixtures/ORM/Load%sData.php',
	            $dir,
	            $shortName
	        );
	        
	        if ($this->filesystem->exists($target) && !$erase)
	        	continue;

    		$columns 				= array();
    		$identifiersColumns 	= array();
    		$associationsColumns 	= array();

    		// Récupération des colonnes (nom, type)
    		foreach ($doctrineEntity->getFieldNames() as $columnName) {
    			if (in_array($columnName, $doctrineEntity->getIdentifierFieldNames())){
    				$identifiersColumns[] = array(
	    				'columnName' => $columnName,
	    				'propertyName' => ucfirst($columnName),
	    				'columnType' => $doctrineEntity->getTypeOfField($columnName)
	    			);
    			}    			
    			else {
	    			$columns[] = array(
	    				'columnName' => $columnName,
	    				'propertyName' => ucfirst($columnName),
	    				'columnType' => $doctrineEntity->getTypeOfField($columnName)
	    			);	    			
    			}
    		}
   
   			// Récupération des FOREIGN KEY 
    		foreach ($doctrineEntity->getAssociationNames() as $association) {
                if ($doctrineEntity->isAssociationInverseSide($association))
                    continue;

                $associationsColumns[] = array(
	    			'columnName' 			=> $association,
	    			'propertyName' 			=> ucfirst($association),
	    			'propertyNameCollecion' => substr(ucfirst($association), 0, -1),
	    			'columnType' 			=> $doctrineEntity->isCollectionValuedAssociation($association),
	    			'target_entity' 		=> $metadataFactory->getReflectionService()->getClassShortName($doctrineEntity->getAssociationTargetClass($association))
	    		);
            }

	    	$this->renderFile('fixtures/fixtures.php.twig', $target, array(
	            'namespace' 		=> $bundle->getNamespace(),
	            'entity_namespace' 	=> $doctrineEntity->getName(),
	            'entity_name' 		=> $shortName,
	            'data' 				=> $entity['data'],
	            'columns' 			=> $columns,
	            'order' 			=> $entity['order'],
	            'identifiers'		=> $identifiersColumns,
	            'associations'		=> $associationsColumns
	        ));

	        $generatedFixtures[] = $target;
    	}

    	return $generatedFixtures;
    }
}