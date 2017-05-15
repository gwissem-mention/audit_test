<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Type;
use Search\Model\Query;
use Search\Model\User;

class ContentTypeFactory implements TypeFactoryInterface
{
    const TYPE = "content";

    public function getQuery(Query $source, User $user)
    {
        $query = (new BoolQuery())
            ->addMust(
                (new \Elastica\Query\MultiMatch())
                    ->setFields(['title', 'content'])
                    ->setType(\Elastica\Query\MultiMatch::TYPE_BEST_FIELDS)
                    ->setQuery($source->getTerm())
                    ->setFuzziness(\Elastica\Query\MultiMatch::FUZZINESS_AUTO)
            )
            ->addMust(
                (new Type(self::TYPE))
            )
        ;

        return $query;
    }
}
