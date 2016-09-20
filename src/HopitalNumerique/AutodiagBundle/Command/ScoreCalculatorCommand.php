<?php

namespace HopitalNumerique\AutodiagBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScoreCalculatorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('autodiag:score:compute')
            ->setDescription('Compute all entry scores')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $syntheses = $this->getContainer()->get('autodiag.repository.synthesis')->findAll();

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
//        $em->beginTransaction();
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
//        $em->commit();
    }
}
