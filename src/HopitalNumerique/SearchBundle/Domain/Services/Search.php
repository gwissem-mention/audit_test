<?php

namespace HopitalNumerique\SearchBundle\Domain\Services;

use Elastica\Aggregation;
use Elastica\Query;
use FOS\ElasticaBundle\Elastica\Index;

class Search
{
    /**
     * @var Index
     */
    protected $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    /**
     * @param $term
     *
     * @return \Elastica\ResultSet
     */
    public function search($term)
    {
        $query = new Query();
        $query->setQuery(
//            new Query\Match('titre', $term)
//            new Query\MatchAll('titre', $term)
            (new Query\MultiMatch())
                ->setFields([
                    'titre',
                    'title',
                    'body',
                ])
                ->setType(Query\MultiMatch::TYPE_BEST_FIELDS)
                ->setQuery($term)
        );

        $query->addAggregation(
            (new Aggregation\Terms('types'))
                ->setField('_type')
                ->addAggregation(
                    (new Aggregation\Nested('subtypes', 'types'))
                        ->addAggregation(
                            (new Aggregation\Terms('types_label'))
                                ->setField('types.libelle')
                        )
                )
        );


        $results = $this->index->search(
            $query->setSize(10)
        );

        return $results;
    }
}
