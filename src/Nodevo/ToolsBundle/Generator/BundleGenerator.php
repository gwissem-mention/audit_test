<?php

namespace Nodevo\ToolsBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Container;

use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator as Generator;

/**
 * Generates a bundle.
 */
class BundleGenerator extends Generator
{
    private $filesystem;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate($namespace, $bundle, $dir, $format, $structure)
    {
        parent::generate($namespace, $bundle, $dir, $format, $structure);

        $dir .= '/'.strtr($namespace, '\\', '/');
        //remove Test Folder ... we don't use it for the moment
        $this->filesystem->remove($dir.'/Tests');
    }
}