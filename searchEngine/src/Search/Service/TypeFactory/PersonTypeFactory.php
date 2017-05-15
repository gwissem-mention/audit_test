<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use Elastica\Query\Type;
use Search\Model\Query;
use Search\Model\User;

class PersonTypeFactory implements TypeFactoryInterface
{
    const TYPE = "person";

    public function getQuery(Query $source, User $user)
    {
        $query = (new BoolQuery())
            ->addMust(
                (new \Elastica\Query\MultiMatch())
                    ->setFields(['firstname', 'lastname'])
                    ->setType(\Elastica\Query\MultiMatch::TYPE_CROSS_FIELDS)
                    ->setTieBreaker(1.0)
                    ->setQuery($source->getTerm())
            )
            ->addMust(
                (new Type(self::TYPE))
            )
        ;

        return $query;
    }
}
