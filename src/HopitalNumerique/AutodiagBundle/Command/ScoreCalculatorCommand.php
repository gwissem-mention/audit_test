<?php

namespace HopitalNumerique\AutodiagBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScoreCalculatorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('autodiag:score:compute')
            ->setDescription('Compute all entry scores')
            ->addArgument('synthesis', InputArgument::OPTIONAL, 'Synthesis ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $synthesis = $input->getArgument('synthesis');
        if (null === $synthesis) {
            $syntheses = $this->getContainer()->get('autodiag.repository.synthesis')->findAll();
        } else {
            $syntheses = [
                $this->getContainer()->get('autodiag.repository.synthesis')->find($synthesis)
            ];
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $i = 0;
        foreach ($syntheses as $synthesis) {
            $output->writeln(
                sprintf('Computing score for synthesis "%s".', $synthesis->getName())
            );
            $this->getContainer()->get('autodiag.score_calculator')->computeSynthesisScore($synthesis, false);

            if ($i++ % 100 === 0) {
                $em->flush();
            }
        }
        $em->flush();
    }
}
