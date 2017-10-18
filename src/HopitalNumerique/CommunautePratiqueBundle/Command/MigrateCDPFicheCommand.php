<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Command;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

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

            /** @var Commentaire $ficheReply */
            foreach ($fiche->getCommentaires() as $ficheReply) {
                $output->write('.');
                $comment = new Message($discussion, $ficheReply->getMessage(), $ficheReply->getUser());
                $comment->setCreatedAt($ficheReply->getDateCreation());

                $entityManager->persist($comment);
            }

            $output->write(' => ');

            $entityManager->flush();

            $output->writeln('[SAVED]');
        }
    }
}
