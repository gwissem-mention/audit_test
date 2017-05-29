<?php

namespace HopitalNumerique\AutodiagBundle\Command;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateActionPlanCommand
 */
class MigrateActionPlanCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('autodiag:actionplan:migrate')
            ->setDescription('Changes the links storage location for action plans.')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $actionPlans = $em->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\ActionPlan')->findAll();

        /** @var Autodiag\ActionPlan $actionPlan */
        foreach ($actionPlans as $actionPlan) {
            $url = $actionPlan->getLink();
            $description = $actionPlan->getLinkDescription();

            if (null != $url) {
                $link = new Autodiag\ActionPlan\Link($actionPlan, $url, $description);
                $actionPlan->addLink($link);
            }

            $em->flush();
        }

        return true;
    }
}
