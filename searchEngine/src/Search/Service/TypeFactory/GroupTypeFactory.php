<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Type;
use Search\Model\Query;
use Search\Model\User;

class GroupTypeFactory implements TypeFactoryInterface
{
    const TYPE = "cdp_groups";

    public function getQuery(Query $source, User $user)
    {
        $query = (new BoolQuery())
            ->addMust(
                (new \Elastica\Query\MultiMatch())
                    ->setFields(['title', 'descriptionCourte'])
                    ->setType(\Elastica\Query\MultiMatch::TYPE_BEST_FIELDS)
                    ->setQuery($source->getTerm())
                    ->setOperator(\Elastica\Query\MultiMatch::OPERATOR_AND)
                    ->setFuzziness(\Elastica\Query\MultiMatch::FUZZINESS_AUTO)
                    ->setPrefixLength(2)
            )
            ->addMust(
                (new Type(self::TYPE))
            )
        ;

        return $query;
    }
}
