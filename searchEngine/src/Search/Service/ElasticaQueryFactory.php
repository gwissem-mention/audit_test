<?php

namespace Search\Service;

use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use Search\Model\Query;
use Search\Model\User;
use Search\Service\TypeFactory\TypeFactoryInterface;

/**
 * Elastica Query factory.
 * Take care of converter Query to Elastica Query
 */
class ElasticaQueryFactory
{
    /**
     * @var TypeFactoryInterface[]
     */
    protected $typeFactories = [];

    /**
     * Get Elastic Query based on source Query
     *
     * @param Query $source
     * @param User $user
     *
     * @return \Elastica\Query
     */
    public function getQuery(Query $source, User $user)
    {
        $rootQuery = new \Elastica\Query();
        $query = (new \Elastica\Query\BoolQuery())
            ->addMust(
                $this->createTermQuery($source, $user)
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

        $rootQuery->setHighlight([
            'fields' => [
                'title' => [
                    'type' => 'fvh',
                    'number_of_fragments' => 0,
                    'no_match_size' => 350,
                    'matched_fields' => ['title', 'title.exact'],
                ],
                'chapter_label' => [
                    'type' => 'fvh',
                    'number_of_fragments' => 0,
                    'no_match_size' => 350,
                    'matched_fields' => ['chapter_label', 'chapter_label.exact'],
                ],
                'content' => [
                    'type' => 'fvh',
                    'number_of_fragments' => 1,
                    'fragment_size' => 350,
                    'no_match_size' => 350,
                    // Available in elasticsearch 5.4
                    //'boundary_scanner' => ['sentence'],
                    //'boundary_scanner_locale' => 'fr',
                ],
            ]
        ]);

        $rootQuery->setSize($source->getSize());
        $rootQuery->setFrom($source->getFrom());

        return $rootQuery;
    }

    public function addTypeFactory(TypeFactoryInterface $typeFactory)
    {
        $this->typeFactories[] = $typeFactory;
    }

    /**
     * Build term search query
     *
     * @param Query $source
     * @param User $user
     *
     * @return BoolQuery
     */
    private function createTermQuery(Query $source, User $user)
    {
        $query = new \Elastica\Query\BoolQuery();

        foreach ($this->typeFactories as $typeFactory) {
            $query->addShould($typeFactory->getQuery($source, $user));
        }

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
