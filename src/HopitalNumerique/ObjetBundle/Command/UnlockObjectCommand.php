<?php

namespace HopitalNumerique\ObjetBundle\Command;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnlockObjectCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hn:object:unlock')
            ->setDescription('Unlocks all objects on the platform.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start of unlocking objects.');

        try {
            $container = $this->getContainer();

            $em = $container->get('doctrine.orm.entity_manager');

            $objects = $container->get('hopitalnumerique_objet.repository.objet')->findAll();

            /** @var Objet $object */
            foreach ($objects as $object) {
                if ($object->getLock()) {
                    $object->setLock(false);
                }
            }

            $em->flush();

            $output->writeln('Done.');
        } catch (\Exception $exception) {
            $output->writeln('An error has occurred.');
        }
    }
}
