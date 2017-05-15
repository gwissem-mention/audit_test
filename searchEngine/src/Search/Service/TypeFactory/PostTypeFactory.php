<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\Type;
use Search\Model\Query;
use Search\Model\User;

class PostTypeFactory implements TypeFactoryInterface
{
    const TYPE = "forum_post";

    public function getQuery(Query $source, User $user)
    {
        $query = (new BoolQuery())
            ->addMust(
                (new Match())
                    ->setFieldQuery('content', $source->getTerm())
                    ->setFieldFuzziness('content', 'AUTO')
            )
            ->addMust(new Type(self::TYPE))
        ;

        return $query;
    }
}
