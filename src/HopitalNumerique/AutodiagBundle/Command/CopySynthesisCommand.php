<?php

namespace HopitalNumerique\AutodiagBundle\Command;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CopySynthesisCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('autodiag:synthesis:copy')
            ->setDescription('Copy synthesis')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('synthesis', InputArgument::REQUIRED, 'Synthesis id'),
                    new InputOption('name', 'r', InputOption::VALUE_OPTIONAL, 'Copied name')
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $synthesis = $input->getArgument('synthesis');
        /** @var Synthesis $synthesis */
        $synthesis = $this->getContainer()->get('autodiag.repository.synthesis')->find($synthesis);

        $copy = Synthesis::copySynthesis($synthesis);

        if (null !== ($name = $input->getOption('name'))) {
            $copy->setName($name);
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->persist($copy);
        $em->flush();

        $this->getContainer()->get('autodiag.score_calculator')->computeSynthesisScore($copy);
        $this->getContainer()->get('autodiag.score_boundary_calculator')->computeBoundaries($copy);

        return true;
    }
}
