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

class ScoreCalculatorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('autodiag:score:compute')
            ->setDescription('Compute all entry scores')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('autodiag', 'a', InputOption::VALUE_OPTIONAL),
                    new InputOption('synthesis', 'sy', InputOption::VALUE_OPTIONAL),
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();

        $synthesis = $options['synthesis'];
        if (null !== $synthesis) {
            $synthesis = $this->getContainer()->get('autodiag.repository.synthesis')->find($synthesis);

            if ($synthesis) {
                $this->getContainer()->get('autodiag.score_calculator')->computeSynthesisScore($synthesis);
            }
            return true;
        }


        $autodiag = $options['autodiag'];
        if (null !== $autodiag) {
            $autodiags = [
                $this->getContainer()->get('autodiag.repository.autodiag')->find($autodiag)
            ];
        } else {
            $autodiags = $this->getContainer()->get('autodiag.repository.autodiag')->findAll();
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach ($autodiags as $autodiag) {
            /** @var Autodiag $autodiag */

            $computingBeginning = $autodiag->setComputing();
            $em->flush();

            $syntheses = $this->getContainer()->get('autodiag.repository.synthesis')->findBy([
                'autodiag' => $autodiag,
            ]);

            $this->getContainer()->get('autodiag.repository.synthesis')->markAsComputingByAutodiag($autodiag);

            $breaked = false;
            foreach ($syntheses as $synthesis) {

                $computing = $this->getContainer()->get('autodiag.repository.autodiag')->getComputeBeginning(
                    $autodiag->getId()
                );

                if ($computing != $computingBeginning) {
                    $breaked = true;
                    break;
                }

                $output->writeln(
                    sprintf('Computing score for synthesis "%s".', $synthesis->getName())
                );
                $this->getContainer()->get('autodiag.score_calculator')->computeSynthesisScore($synthesis);

            }

            if (!$breaked) {
                $autodiag->stopComputing();
                $em->flush();
            }
        }

        return true;
    }
}
