<?php
namespace Nodevo\ErrorsBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;

/**
 * NodevoErrorsExceptionInstallCommand.
 */
class NodevoErrorsExceptionInstallCommand extends Command
{
    protected $container;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->container = $this->getApplication()->getKernel()->getContainer();
    }
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Lien symbolique vers la page d\'exception au lieu de la copier')
            ->setName('nodevo:install:exceptions')
            ->setDescription('Change les pages d\'erreurs de symfony')
        ;
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = $this->container->get('filesystem');

        // Create the bundles directory otherwise symlink will fail.
        if (is_dir($originDir = __DIR__.'/../Resources/views/TwigBundle')) {
            $output->writeln(sprintf('Installation des pages d\'erreurs vers <comment>app/Resources/TwigBundle</comment>'));

            $targetDir = $this->container->getParameter('kernel.root_dir').'/Resources/TwigBundle';

            $filesystem->remove($targetDir);

            if ($input->getOption('symlink')) {
                $filesystem->symlink($originDir, $targetDir);
            } else {
                $filesystem->mkdir($targetDir, 0777);
                $filesystem->mirror($originDir, $targetDir);
            }
        }
    }
}