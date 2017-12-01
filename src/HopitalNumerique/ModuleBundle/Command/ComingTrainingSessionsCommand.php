<?php

namespace HopitalNumerique\ModuleBundle\Command;

use HopitalNumerique\ModuleBundle\Event\ComingTrainingSessionsEvent;
use HopitalNumerique\ModuleBundle\Events;
use HopitalNumerique\ModuleBundle\HopitalNumeriqueModuleBundle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ComingTrainingSessionsCommand.
 */
class ComingTrainingSessionsCommand extends ContainerAwareCommand
{
    /**
     * Period used to notify about new sessions. Training Sessions starting later will not be notified.
     * Must be a valid DateInterval string.
     */
    const NOTIFY_MAX_INTERVAL = '3 months';

    protected function configure()
    {
        $this
            ->setName('notify-coming-training-sessions')
            ->setDescription('Trigger notifications about coming training sessions.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Build notify max date
        $today = new \DateTime();
        $notifyInterval = \DateInterval::createFromDateString(self::NOTIFY_MAX_INTERVAL);
        $notifyDate = $today->add($notifyInterval);

        //Get needed repositories
        $moduleRepo = $this->getContainer()->get('hn.module.repository.module');
        $sessionRepo = $this->getContainer()->get('hn.module.repository.session');

        //Get modules concerned by new sessions notification event.
        $modulesId = HopitalNumeriqueModuleBundle::MODULE_TO_BE_NOTIFIED;
        $modules = $moduleRepo->getModules($modulesId);

        //Browse modules sessions between today and $notifyDate and dispatch 'COMING_TRAINING_SESSION' event for each.
        foreach ($modules as $module) {
            $sessions = $sessionRepo->getComingSessionsForModule($module, $notifyDate);
            foreach ($sessions as $session) {
                $event = new ComingTrainingSessionsEvent($session);
                $this->getContainer()->get('event_dispatcher')->dispatch(Events::COMING_TRAINING_SESSION, $event);
            }
        }

        return 1;
    }
}
