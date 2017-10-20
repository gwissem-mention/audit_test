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
use HopitalNumerique\QuestionnaireBundle\Repository\QuestionnaireRepository;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class MigrateCDPForumGroupBoardCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('app:cdp:migrate:forum:group')
            ->setDescription('Migrate CDP forum group boards')
            ->setHelp('Migrate forum boards messages to group discussions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $boardRepository = $this->getContainer()->get('hopitalnumerique_forum.repository.board');
        $questionnaire = $this->getContainer()->get(QuestionnaireRepository::class)->find(27);

        $users = [];

        $output->writeln('**************************');
        $output->writeln('*** Migrating boards ****');
        $output->writeln('**************************');
        $output->writeln('');

        $boards = $boardRepository->findById([13, 19, 23]);

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

            $group = new Groupe();
            $group
                ->setTitre($board->getName())
                ->setDateCreation(new \DateTime())
                ->setDateDemarrage(new \DateTime())
                ->setDateFin((new \DateTime())->add(new \DateInterval('P1Y')))
                ->setDateInscriptionOuverture(new \DateTime())
                ->setActif(true)
                ->setDescriptionCourte($board->getDescription())
                ->setDescriptionHtml($board->getDescription())
                ->setNombreParticipantsMaximum(20)
                ->setVedette(false)
                ->setQuestionnaire($questionnaire)
            ;

            foreach ($domains as $domain) {
                $group->addDomain($domain);
            }

            $entityManager->persist($group);

            /** @var Topic $topic */
            foreach ($board->getTopics() as $topic) {

                if (null === $topic->getFirstPost()) {
                    continue;
                }

                $output->write(sprintf('        Topic : %s', $topic->getTitle()));

                $discussion = new Discussion($topic->getTitle(), $topic->getFirstPost()->getCreatedBy(), $domains);
                $discussion
                    ->addGroup($group)
                    ->setPublic(false)
                    ->setCreatedAt($topic->getFirstPost()->getCreatedDate())
                ;

                $entityManager->persist($discussion);

                /** @var Post $post */
                foreach ($topic->getPosts() as $post) {
                    $output->write('.');

                    $message = new Message($discussion, nl2br($post->getBody()), $post->getCreatedBy());
                    $message->setCreatedAt($post->getCreatedDate());

                    $entityManager->persist($message);

                    $group->addUser($post->getCreatedBy());

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
                ->setCommunautePratiqueEnrollmentDate($user->getCommunautePratiqueEnrollmentDate() ?: new \DateTime())
                ->setInscritCommunautePratique(true)
            ;
            $output->writeln(' [REGISTERED]');
        }

        $entityManager->flush();
    }
}
