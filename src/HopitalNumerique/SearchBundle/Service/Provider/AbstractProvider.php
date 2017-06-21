<?php

namespace HopitalNumerique\SearchBundle\Service\Provider;

use Doctrine\ORM\EntityRepository;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\ProviderInterface;

/**
 * Abstract index provider for Doctrine ORM based objects
 *
 * @package HopitalNumerique\SearchBundle\Service\Provider
 */
abstract class AbstractProvider implements ProviderInterface
{
    const BATCH_SIZE = 28;

    /**
     * @var string
     */
    protected $domaineSlug;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var ObjectPersisterInterface
     */
    protected $persister;

    /**
     * ObjectProvider constructor.
     *
     * @param $domaineSlug
     * @param EntityRepository $repository
     * @param ObjectPersisterInterface $persister
     */
    public function __construct($domaineSlug, EntityRepository $repository, ObjectPersisterInterface $persister)
    {
        $this->domaineSlug = $domaineSlug;
        $this->repository = $repository;
        $this->persister = $persister;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        $total = $this->countObjects();
        foreach ($this->getData() as $objects) {
            $this->persister->insertMany($objects);
            $loggerClosure(count($objects), $total);
        }
    }

    /**
     * Retrieve data to populate
     *
     * @return \Generator
     */
    protected function getData()
    {
        $page = 0;
        $queryBuilder = $this->createQueryBuidler();

        do {
            $queryBuilder
                ->setFirstResult($page * self::BATCH_SIZE)
                ->setMaxResults(self::BATCH_SIZE)
            ;

            $results = $queryBuilder->getQuery()->getResult();
            if (count($results) > 0) {
                yield $results;
            }
            $currentCount = count($results);
            $page++;

        } while ($currentCount === self::BATCH_SIZE);
    }

    /**
     * Count all objects to populate
     *
     * @return mixed
     */
    protected function countObjects()
    {
        $queryBuilder = $this->createQueryBuidler();
        $rootAliases = $queryBuilder->getRootAliases();

        return $queryBuilder
            ->select($queryBuilder->expr()->count($rootAliases[0]))
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Create a custom query builder
     *
     * @return mixed
     */
    abstract protected function createQueryBuidler();
}
