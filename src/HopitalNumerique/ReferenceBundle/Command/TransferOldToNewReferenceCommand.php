<?php

namespace HopitalNumerique\ReferenceBundle\Command;

use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Entity\ReferenceCode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransferOldToNewReferenceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hn:reference:transfer')
            ->setDescription('Transfer all old references to the new db model.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Transfer started.');

        try {
            $container = $this->getContainer();

            $em = $container->get('doctrine.orm.entity_manager');

            $references = $container->get('hopitalnumerique_reference.manager.reference')->findAll();

            /** @var Reference $reference */
            foreach ($references as $reference) {
                if ($reference->getCode() == '') {
                    continue;
                }
                $referenceCode = new ReferenceCode();
                $referenceCode->setReference($reference);
                $referenceCode->setLabel($reference->getCode());

                $em->persist($referenceCode);

                $reference->addCode($referenceCode);
            }

            $em->flush();
        } catch (\Exception $exception) {
            $output->writeln('An error occurred while transferring references');
        }

        $output->writeln('Transfer completed.');
    }
}
