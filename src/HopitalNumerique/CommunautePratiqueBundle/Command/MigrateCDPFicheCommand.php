<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Command;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\FichierBundle\Entity\File;
use HopitalNumerique\FichierBundle\Service\FilePathFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;

class MigrateCDPFicheCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('app:cdp:migrate:fiche')
            ->setDescription('Migrate CDP fiches')
            ->setHelp('Migrate fiches to discussions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $ficheRepository = $this->getContainer()->get('hopitalnumerique_communautepratique.repository.fiche');

        $output->writeln('************************');
        $output->writeln('*** Migrating fiches ***');
        $output->writeln('************************');
        $output->writeln('');

        /** @var Fiche $fiche */
        foreach ($ficheRepository->findAll() as $fiche) {
            $output->write(sprintf('    Fiche : %s', $fiche->getQuestionPosee()));
            $discussion = new Discussion($fiche->getQuestionPosee(), $fiche->getUser(), $fiche->getGroupe()->getDomains()->toArray());
            $discussion
                ->setCreatedAt($fiche->getDateCreation())
                ->addGroup($fiche->getGroupe())
                ->setPublic(false)
            ;

            $entityManager->persist($discussion);

            $firstMessageContent = sprintf('<p><b>Éléments de contexte à prendre en compte :</b><br />%s</p>', $fiche->getContexte());
            $firstMessageContent .= sprintf('<p><b>Description complète du problème :</b><br />%s</p>', $fiche->getDescription());
            $firstMessageContent .= sprintf('<p><b>Aide attendue :</b><br />%s</p>', $fiche->getAideAttendue());
            $firstMessageContent .= sprintf('<p><b>En résumé...</b><br />%s</p>', $fiche->getResume());

            $output->write('.');

            $message = new Message($discussion, $firstMessageContent, $fiche->getUser());
            $message->setCreatedAt($fiche->getDateCreation());

            $entityManager->persist($message);

            $this->ficheDocuments($fiche, $message);

            /** @var Commentaire $ficheReply */
            foreach ($fiche->getCommentaires() as $ficheReply) {
                $output->write('.');
                $comment = new Message($discussion, $ficheReply->getMessage(), $ficheReply->getUser());
                $comment->setCreatedAt($ficheReply->getDateCreation());

                $entityManager->persist($comment);
                $entityManager->flush();

                $this->addDocuments($ficheReply, $comment);
            }

            $output->write(' => ');

            $entityManager->flush();

            $output->writeln('[SAVED]');
        }
    }

    private function ficheDocuments(Fiche $fiche, Message $message)
    {
        if (0 === $fiche->getDocuments()->count()) {
            return;
        }

        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $filePathFinder = $this->getContainer()->get(FilePathFinder::class);
        $router = $this->getContainer()->get('router');
        $filesystem = new Filesystem();
        $baseDir = $this->getContainer()->getParameter('kernel.root_dir');

        $files = [];
        foreach ($fiche->getDocuments() as $document) {
            $path = $baseDir.'/../'.$document->getPathname();

            if (!$filesystem->exists($path)) {
                continue;
            }

            $name = uniqid().'.'.$document->getExtension();
            $file = new File($document->getLibelle(), $name, $fiche->getUser());
            $file->setActive(true);

            $filesystem->copy($path, $filePathFinder->getUserFolderPath($fiche->getUser()).'/'.$name);

            $entityManager->persist($file);
            $message->addFile($file);

            $entityManager->flush();

            $files[] = '<a href="'.$router->generate(
                'hopitalnumerique_communautepratique_discussions_discussion_message_file',
                [
                    'message' => $message->getId(),
                    'file' => $file->getId(),
                ]
            ).'">'.$document->getLibelle().'</a>';
        }

        $message->setContent($message->getContent().sprintf('<p><b>Fichiers:</b><br /> %s</p>', implode('<br />', $files)));
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
