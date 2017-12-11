<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Command;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Event\Group\GroupRegistrationEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class MigrateCDPREXCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('app:cdp:migrate:rex')
            ->setDescription('Migrate CDP REX')
            ->setHelp('Migrate REX to presentation discussion')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $groupRepository = $this->getContainer()->get('hopitalnumerique_communautepratique.repository.group');

        $output->writeln('**********************');
        $output->writeln('*** Migrating REX ****');
        $output->writeln('**********************');
        $output->writeln('');

        /** @var Groupe $group */
        foreach ($groupRepository->findAll() as $group) {
            $output->write(sprintf('    Group : %s', $group->getTitre()));

            foreach ($group->getInscriptions() as $registration) {

                if (count($answers = $this->getContainer()->get('hopitalnumerique_questionnaire.manager.reponse')
                        ->reponsesByQuestionnaireByUser($group->getQuestionnaire()->getId(), $registration->getUser()->getId())) > 0
                ) {
                    $output->write('.');
                    $this->getContainer()->get('event_dispatcher')->dispatch(
                        Events::GROUP_REGISTRATION,
                        new GroupRegistrationEvent($registration->getUser(), $group, $answers, $group->getDomains()->toArray()));
                }
            }

            $output->write(' => ');

            $entityManager->flush();

            $output->writeln('[SAVED]');
        }
    }
}
