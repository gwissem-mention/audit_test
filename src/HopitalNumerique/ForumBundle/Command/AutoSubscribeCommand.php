<?php

namespace HopitalNumerique\ForumBundle\Command;

use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ForumBundle\Entity\Board;
use HopitalNumerique\ForumBundle\Entity\Category;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @TODO Command to be removed when #7091 is deployed (http://redmine.nodevo.com/issues/7091)
 */
class AutoSubscribeCommand extends ContainerAwareCommand
{
    private $boards = [];

    protected function configure()
    {
        $this
            ->setName('redmine-7091')
            ->setDescription('Subscribe users to domain forum categories related boards')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $entityManager = $container->get('doctrine.orm.default_entity_manager');

        $subscriptionModel = $container->get('ccdn_forum_forum.model.subscription');
        $userRepository = $entityManager->getRepository(User::class);

        $userIterator = $userRepository
            ->createQueryBuilder('user')
            ->andWhere('user.inscritCommunautePratique = true')
            ->getQuery()
            ->iterate()
        ;

        $entityManager->beginTransaction();
        foreach ($userIterator as $user) {
            /** @var User $user */
            $user = $user[0];

            foreach ($user->getDomaines() as $domain) {
                foreach ($this->getBoardsByDomain($domain) as $board) {
                    $subscriptionModel->subscribeBoard($board, $user);
                    $output->writeln(
                        sprintf(
                            'User [%d] "%s" has been subscribed to board [%d] "%s"',
                            $user->getId(),
                            $user->getEmail(),
                            $board->getId(),
                            $board->getName()
                        )
                    );
                }
            }
        }
        $entityManager->commit();
    }

    /**
     * @param Domaine $domain
     *
     * @return Board[]
     */
    private function getBoardsByDomain(Domaine $domain)
    {
        if (array_key_exists($domain->getId(), $this->boards)) {
            return $this->boards[$domain->getId()];
        }

        $repository = $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository(Board::class);

        $categoryIds = $domain->getCommunautePratiqueForumCategories()->map(function (Category $category) {
            return $category->getId();
        })->toArray();

        if (empty($categoryIds)) {
            $this->boards[$domain->getId()] = [];
        } else {
            $qb = $repository->createQueryBuilder('board');
            $this->boards[$domain->getId()] = $qb
                ->join('board.category', 'category', Join::WITH, $qb->expr()->in('category.id', $categoryIds))
                ->getQuery()
                ->getResult()
            ;
        }

        return $this->boards[$domain->getId()];
    }
}
