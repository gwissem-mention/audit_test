<?php

namespace Search\Service;

use Elastica\Query\AbstractQuery;
use Search\Model\Query;

/**
 * Elastica Query factory.
 * Take care of converter Query to Elastica Query
 */
class ElasticaQueryFactory
{
    /**
     * Get Elastic Query based on source Query
     *
     * @param Query $source
     *
     * @return \Elastica\Query
     */
    public function getQuery(Query $source)
    {
        $rootQuery = new \Elastica\Query();
        $query = (new \Elastica\Query\BoolQuery())
            ->addMust(
                $this->createTermQuery($source->getTerm())
            )
        ;

        if (!empty($source->getFilters())) {
            $query->addFilter(
                $this->createFilterQuery($source->getFilters())
            );
        }

        $rootQuery->setQuery($query);

        $rootQuery->addAggregation(
            (new \Elastica\Aggregation\Terms('types'))
                ->setField('_type')
                ->addAggregation(
                    (new \Elastica\Aggregation\Nested('subtypes', 'types'))
                        ->addAggregation(
                            (new \Elastica\Aggregation\Terms('types'))
                                ->setField('types.libelle')
                        )
                )
        );

        $titleHighlight = new \stdClass();
        $titleHighlight->force_source = true;
        $rootQuery->setHighlight([
            'fields' => [
                'title' => $titleHighlight,
                'content' => clone($titleHighlight),
            ]
        ]);

        $rootQuery->setSize($source->getSize());
        $rootQuery->setFrom($source->getFrom());

        return $rootQuery;
    }

    /**
     * Build term search query
     *
     * @param $term
     *
     * @return \Elastica\Query\BoolQuery
     */
    private function createTermQuery($term)
    {
        $query = new \Elastica\Query\BoolQuery();

        $query
            ->addShould(
                (new \Elastica\Query\MultiMatch())
                    ->setFields(['title', 'content', 'forumName'])
                    ->setType(\Elastica\Query\MultiMatch::TYPE_PHRASE)
                    ->setQuery($term)
            )
            ->addShould(
                (new \Elastica\Query\MultiMatch())
                    ->setFields(['firstname', 'lastname'])
                    ->setType(\Elastica\Query\MultiMatch::TYPE_CROSS_FIELDS)
                    ->setTieBreaker(1.0)
                    ->setQuery($term)
            )
        ;

        return $query;
    }

    /**
     * Builde filters
     *
     * @param Query\Filter[] $filters
     *
     * @return \Elastica\Query\BoolQuery
     */
    private function createFilterQuery($filters)
    {
        $filterQuery = new \Elastica\Query\BoolQuery();

        foreach ($filters as $filter) {
            call_user_func(
                [$filterQuery, $filter->isNegative() ? 'addMustNot' : 'addMust'],
                $this->createNestedFilterQuery($filter)
            );
        }

        return $filterQuery;
    }

    /**
     * Recursive function to create nested filters query
     *
     * @param Query\Filter $filter
     * @param null $parts
     *
     * @return AbstractQuery
     */
    private function createNestedFilterQuery(Query\Filter $filter, $parts = null)
    {
        if (null === $parts) {
            $parts = explode('.', $filter->getField());
        }

        if (count($parts) > 1) {
            return (new \Elastica\Query\Nested())
                ->setPath(array_shift($parts))
                ->setQuery(
                    $this->createNestedFilterQuery($filter, $parts)
                )
            ;
        }

        return (new \Elastica\Query\Term())
            ->setParam($filter->getField(), $filter->getValue())
        ;
    }
}
