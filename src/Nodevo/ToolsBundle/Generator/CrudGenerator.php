<?php

namespace Nodevo\ToolsBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\DoctrineCrudGenerator as Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Generates a CRUD controller.
 */
class CrudGenerator extends Generator
{
    /**
     * Generate the CRUD controller.
     *
     * @param BundleInterface   $bundle           A bundle object
     * @param string            $entity           The entity relative class name
     * @param ClassMetadataInfo $metadata         The entity class metadata
     * @param string            $format           The configuration format (xml, yaml, annotation)
     * @param string            $routePrefix      The route name prefix
     * @param array             $needWriteActions Wether or not to generate write actions
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata, $format, $routePrefix, $needWriteActions, $forceOverwrite)
    {
        parent::generate($bundle, $entity, $metadata, $format, $routePrefix, $needWriteActions, $forceOverwrite);

        $dir = $this->bundle->getPath();

        //Manager custom stuff
        $this->filesystem->mkdir($dir.'/Manager');
        $this->generateManagerClass();

        //Grid custom stuff
        $this->filesystem->mkdir($dir.'/Grid');
        $this->generateGridClass();

        //add Services for Grid, Manager and Form
        $this->generateServices();
        
        //remove Test Folder ... we don't use it for the moment
        $this->filesystem->remove($dir.'/Tests');
    }

    /**
     * Override de la configuration de Sensio : on met toutes les routes dans le fichier du bundle
     */
    protected function generateServices()
    {
        $target = sprintf(
            '%s/Resources/config/services.yml',
            $this->bundle->getPath()
        );

        $this->renderFile('crud/config/services.'.$this->format.'.twig', $target, array(
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'namespace'         => $this->bundle->getNamespace(),
        ), true);
    }


    /**
     * Generate the Manager Object
     */
    protected function generateManagerClass()
    {
        $dir             = $this->bundle->getPath();
        $parts           = explode('\\', $this->entity);
        $entityClass     = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $target = sprintf(
            '%s/Manager/%s/%sManager.php',
            $dir,
            str_replace('\\', '/', $entityNamespace),
            $entityClass
        );

        $this->renderFile('crud/manager.php.twig', $target, array(
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'entity_class'      => $entityClass,
            'namespace'         => $this->bundle->getNamespace(),
            'entity_namespace'  => $entityNamespace,
            'format'            => $this->format,
        ));
    }

    /**
     * Generate the Grid Object from NodevoGridBundle
     */
    protected function generateGridClass()
    {
        $dir             = $this->bundle->getPath();
        $parts           = explode('\\', $this->entity);
        $entityClass     = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $target = sprintf(
            '%s/Grid/%s/%sGrid.php',
            $dir,
            str_replace('\\', '/', $entityNamespace),
            $entityClass
        );

        $this->renderFile('crud/grid.php.twig', $target, array(
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'entity_class'      => $entityClass,
            'fields'            => $this->metadata->fieldMappings,
            'namespace'         => $this->bundle->getNamespace(),
            'entity_namespace'  => $entityNamespace,
            'format'            => $this->format,
        ));
    }

    /**
     * Generates the show.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateShowView($dir)
    {
        $this->renderFile('crud/views/show.html.twig.twig', $dir.'/show.html.twig', array(
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'fields'            => $this->metadata->fieldMappings,
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'namespace'         => $this->bundle->getNamespace(),
        ));
    }

    /**
     * Generates the edit.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateEditView($dir)
    {
        $this->renderFile('crud/views/edit.html.twig.twig', $dir.'/edit.html.twig', array(
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'entity'            => $this->entity,
            'bundle'            => $this->bundle->getName(),
            'actions'           => $this->actions,
            'namespace'         => $this->bundle->getNamespace(),
        ));
    }


    /**
     * Generates the index.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateIndexView($dir)
    {
        $this->renderFile('crud/views/index.html.twig.twig', $dir.'/index.html.twig', array(
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'fields'            => $this->metadata->fieldMappings,
            'actions'           => $this->actions,
            'record_actions'    => $this->getRecordActions(),
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'namespace'         => $this->bundle->getNamespace(),
        ));
    }

    /**
     * Generates the add.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateNewView($dir)
    {
        
    }

    /**
     * Override de la configuration de Sensio : on met toutes les routes dans le fichier du bundle
     */
    protected function generateConfiguration()
    {
        if (!in_array($this->format, array('yml', 'xml', 'php'))) {
            return;
        }

        $target = sprintf(
            '%s/Resources/config/routing.%s',
            $this->bundle->getPath(),
            $this->format
        );

        $this->renderFile('crud/config/routing.'.$this->format.'.twig', $target, array(
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
            'namespace'         => $this->bundle->getNamespace(),
        ), true);
    }

    /**
     * Override du render File de Sensio : on ajoute une option qui permet de faire un ajout de contenu au lieu d'écraser le fichier
     */
    protected function renderFile($template, $target, $parameters, $append = false)
    {
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        if( !$append )
            return file_put_contents($target, $this->render($template, $parameters) );
        else
            return file_put_contents($target, $this->render($template, $parameters), FILE_APPEND );
    }
}