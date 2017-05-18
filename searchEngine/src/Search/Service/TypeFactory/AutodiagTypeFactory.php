<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\Type;
use Search\Model\Query;
use Search\Model\User;

class AutodiagTypeFactory implements TypeFactoryInterface
{
    const TYPE = "autodiag";

    public function getQuery(Query $source, User $user)
    {
        $query = (new BoolQuery())
            ->addMust(
                (new Match())
                    ->setFieldQuery('title', $source->getTerm())
                    ->setFieldFuzziness('title', 'AUTO')
                    ->setFieldPrefixLength('title', 2)
            )
            ->addMust(new Type(self::TYPE))
        ;

        return $query;
    }
}
