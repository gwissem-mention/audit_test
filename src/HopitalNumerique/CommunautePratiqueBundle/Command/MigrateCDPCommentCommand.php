<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Command;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

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
        $commentRepository = $this->getContainer()->get('hopitalnumerique_communautepratique.repository.commentaire');
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
            }

            $output->write(' => ');

            $entityManager->flush();

            $output->writeln('[SAVED]');
        }
    }
}
