<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Command;

use HopitalNumerique\ForumBundle\Entity\Board;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class RemoveBoardsCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('app:cdp:migrate:remove:boards')
            ->setDescription('Remove old boards')
            ->setHelp('Remove old boards')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $boardRepository = $this->getContainer()->get('hopitalnumerique_forum.repository.board');
        $categoryRepository = $this->getContainer()->get('ccdn_forum_forum.repository.category');
        $boards = $boardRepository->findById([29, 7, 51, 52]);

        $boards = array_merge($boards, $categoryRepository->findOneCategoryById(1)->getBoards()->toArray());

        /** @var Board $board */
        foreach ($boards as $board) {
            foreach ($board->getTopics() as $topic) {
                $entityManager->remove($topic);
            }

            $entityManager->flush();
        }
    }
}
