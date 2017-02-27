<?php

namespace Search\Service;

use Elastica\Client;
use Search\Model\Query;

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
     *
     * @return array
     */
    public function search(Query $query)
    {
        $query
            ->addFilter(
                new Query\Filter('types.libelle', self::HOT_KEYWORD, true)
            )
        ;

        return $this->doRequest($query);
    }

    /**
     * Search for all "Point dur"
     *
     * @param Query $query
     *
     * @return array
     */
    public function searchHot(Query $query)
    {
        $query
            ->addFilter(
                new Query\Filter('types.libelle', self::HOT_KEYWORD)
            )
        ;

        return $this->doRequest($query, 'object');
    }

    /**
     * @param Query $query
     * @param null $type
     *
     * @return array
     */
    protected function doRequest(Query $query, $type = null)
    {
        $elasticaQuery = $this->factory->getQuery($query);

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
