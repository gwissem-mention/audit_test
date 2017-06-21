<?php

namespace Search\Service;

use Elastica\Client;
use Search\Model\Query;
use Search\Model\User;

/**
 * Search repository
 */
class SearchRepository
{
    const HOT_KEYWORD = "Point dur";

    /**
     * @var ElasticaQueryFactory
     */
    protected $factory;

    /**
     * @var Client
     */
    protected $client;

    /**
     * SearchRepository constructor.
     * @param ElasticaQueryFactory $factory
     * @param Client $client
     */
    public function __construct(ElasticaQueryFactory $factory, Client $client)
    {
        $this->factory = $factory;
        $this->client = $client;
    }

    /**
     * Search all
     *
     * @param Query $query
     * @param User $user
     *
     * @return array
     */
    public function search(Query $query, User $user)
    {
        $query
            ->addFilter(
                new Query\Filter('types.libelle', self::HOT_KEYWORD, true)
            )
        ;

        return $this->doRequest($query, $user);
    }

    /**
     * Search for all "Point dur"
     *
     * @param Query $query
     * @param User $user
     *
     * @return array
     */
    public function searchHot(Query $query, User $user)
    {
        $query
            ->addFilter(
                new Query\Filter('types.libelle', self::HOT_KEYWORD)
            )
        ;

        return $this->doRequest($query, $user, 'object');
    }

    /**
     * @param Query $query
     * @param User $user
     * @param null $type
     *
     * @return array
     */
    protected function doRequest(Query $query, User $user, $type = null)
    {
        $elasticaQuery = $this->factory->getQuery($query, $user);

        $search = new \Elastica\Search($this->client);
        $search
            ->addIndex($query->getIndex())
        ;

        if (null !== $type) {
            $search->addType($type);
        }

        $search->setQuery($elasticaQuery);

        return $search->search()->getResponse()->getData();
    }
}
