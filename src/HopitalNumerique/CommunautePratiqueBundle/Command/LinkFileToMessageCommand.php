<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use HopitalNumerique\FichierBundle\Repository\FileRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository;

class LinkFileToMessageCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('app:cdp:link:file')
            ->setDescription('File file to messages')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $messageRepository = $this->getContainer()->get(MessageRepository::class);
        $fileRepository = $this->getContainer()->get(FileRepository::class);

        $output->writeln('*****************************');
        $output->writeln('*** Link file to message ****');
        $output->writeln('*****************************');
        $output->writeln('');

        /** @var Message $message */
        foreach ($messageRepository->findAll() as $message) {
            $output->write('.');

            preg_match_all(
                '/\/communaute-de-pratiques\/discussion\/message\/[\d]+\/file\/([\d]*)/',
                $message->getContent(),
                $results
            );

            if (count($results[0])) {
                foreach ($results[1] as $fileId) {
                    if (null !== ($file = $fileRepository->find($fileId))) {
                        $output->write('X');
                        $message->addFile($file);
                    }
                }

                $entityManager->flush();
            }
        }
    }
}
