<?php

namespace HopitalNumerique\StatBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckUrlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('stat:url:check')
            ->setDescription('Check all http status for URL')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('URL check begins.'));

        $this->getContainer()->get('stat.service.url_checker')->check();

        $output->writeln(sprintf('All URLs have been checked.'));

        return true;
    }
}
