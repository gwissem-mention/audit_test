<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Command;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\FichierBundle\Entity\File;
use HopitalNumerique\FichierBundle\Service\FilePathFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;

class MigrateCDPCommentCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('app:cdp:migrate:comments')
            ->setDescription('Migrate CDP comments')
            ->setHelp('Migrate comments to discussions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $groupRepository = $this->getContainer()->get('hopitalnumerique_communautepratique.repository.group');

        $output->writeln('***************************');
        $output->writeln('*** Migrating comments ****');
        $output->writeln('***************************');
        $output->writeln('');

        /** @var Groupe $group */
        foreach ($groupRepository->findAll() as $group) {
            if ($group->getCommentaires()->count() === 0) {
                continue;
            }

            $comment = $group->getCommentaires()->last();

            $output->write(sprintf('    Comment of group : %s', $group->getTitre()));
            $discussion = new Discussion('Discussion entre les membres du groupe', $comment->getUser(), $group->getDomains()->toArray());
            $discussion
                ->setCreatedAt($comment->getDateCreation())
                ->addGroup($group)
                ->setPublic(false)
            ;

            $entityManager->persist($discussion);

            foreach ($group->getCommentaires() as $comment) {
                $output->write('.');

                $message = new Message($discussion, $comment->getMessage(), $comment->getUser());
                $message->setCreatedAt($comment->getDateCreation());

                $entityManager->persist($message);

                $this->addDocuments($comment, $message);
            }

            $output->write(' => ');

            $entityManager->flush();

            $output->writeln('[SAVED]');
        }
    }

    /**
     * @param Commentaire $comment
     * @param Message $message
     */
    private function addDocuments(Commentaire $comment, Message $message)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $filePathFinder = $this->getContainer()->get(FilePathFinder::class);
        $documentRepository = $entityManager->getRepository(Document::class);
        $router = $this->getContainer()->get('router');
        $filesystem = new Filesystem();
        $baseDir = $this->getContainer()->getParameter('kernel.root_dir');

        $documentLink = '/\/communaute-de-pratiques\/document\/document\/([\d]+)\/download/';

        if (0 === preg_match_all($documentLink, $message->getContent(), $documents)) {
            return;
        }

        if (count($documents[0])) {
            $entityManager->flush();

            foreach ($documents[0] as $k => $v) {
                /** @var Document $document */
                $document = $documentRepository->find($documents[1][$k]);

                if (!$document) {
                    continue;
                }

                $path = $baseDir.'/../'.$document->getPathname();

                if (!$filesystem->exists($path)) {
                    continue;
                }

                $name = uniqid().'.'.$document->getExtension();
                $file = new File($document->getLibelle(), $name, $comment->getUser());
                $file->setActive(true);

                $filesystem->copy($path, $filePathFinder->getUserFolderPath($comment->getUser()).'/'.$name);

                $entityManager->persist($file);
                $message->addFile($file);

                $entityManager->flush();

                $uri = $router->generate(
                    'hopitalnumerique_communautepratique_discussions_discussion_message_file',
                    [
                        'message' => $message->getId(),
                        'file' => $file->getId(),
                    ]
                );

                $message->setContent(str_replace($v, $uri, $message->getContent()));
            }
        }
    }
}
