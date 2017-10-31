<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Command;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\ForumBundle\Entity\Board;
use HopitalNumerique\ForumBundle\Entity\Post;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class MigrateCDPForumPublicBoardCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('app:cdp:migrate:forum:public')
            ->setDescription('Migrate CDP forum public boards')
            ->setHelp('Migrate forum boards messages to public discussions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $boardRepository = $this->getContainer()->get('hopitalnumerique_forum.repository.board');
        $categoryRepository = $this->getContainer()->get('ccdn_forum_forum.repository.category');
        $bbCodeParser = $this->getContainer()->get('ccdn_component_bb_code.component.bootstrap');

        $users = [];

        $output->writeln('**************************');
        $output->writeln('*** Migrating boards ****');
        $output->writeln('**************************');
        $output->writeln('');

        $boards = $boardRepository->findById([29, 7, 51, 52]);

        $boards = array_merge($boards, $categoryRepository->findOneCategoryById(1)->getBoards()->toArray());

        /** @var Board $board */
        foreach ($boards as $board) {

            $domains = array_filter(
                array_unique(array_merge($board->getCategory()->getDomaines()->toArray(), [$board->getCategory()->getForum()->getDomain()])),
                function ($value) {
                    return null !== $value;
                }
            );

            if (0 === count($domains)) {
                continue;
            }

            $output->writeln(sprintf('    Board : %s', $board->getName()));

            /** @var Topic $topic */
            foreach ($board->getTopics() as $topic) {

                if (null === $topic->getFirstPost()) {
                    continue;
                }

                $output->write(sprintf('        Topic : %s', $topic->getTitle()));

                $discussion = new Discussion($topic->getTitle(), $topic->getFirstPost()->getCreatedBy(), $domains);
                $discussion
                    ->setCreatedAt($topic->getFirstPost()->getCreatedDate())
                ;

                $entityManager->persist($discussion);

                /** @var Post $post */
                foreach ($topic->getPosts() as $post) {
                    $output->write('.');

                    $message = new Message(
                        $discussion,
                        str_replace(['<pre>', '</pre>'], '', nl2br($bbCodeParser->process($post->getBody()))),
                        $post->getCreatedBy()
                    );
                    $message->setCreatedAt($post->getCreatedDate());

                    $entityManager->persist($message);

                    $users[$post->getCreatedBy()->getId()] = $post->getCreatedBy();
                }

                $output->write(' => ');

                $entityManager->flush();

                $output->writeln('[SAVED]');
            }

            $output->writeln('');
        }

        $output->writeln('');
        $output->writeln('*****************************');
        $output->writeln('*** CDP auto-registration ***');
        $output->writeln('*****************************');
        $output->writeln('');

        /** @var User $user */
        foreach ($users as $user) {
            $output->write(sprintf('    User : %s =>', $user->getPrenomNom()));
            $user
                ->setCommunautePratiqueEnrollmentDate($user->getCommunautePratiqueEnrollmentDate() ?: $user->getRegistrationDate())
                ->setInscritCommunautePratique(true)
            ;
            $output->writeln(' [REGISTERED]');
        }

        $entityManager->flush();
    }
}
