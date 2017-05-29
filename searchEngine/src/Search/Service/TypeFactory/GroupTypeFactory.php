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
        $bool = new BoolQuery();
        $bool->addShould(
            (new \Elastica\Query\MultiMatch())
                ->setFields(['title.exact^1.5', 'content.exact'])
                ->setType(\Elastica\Query\MultiMatch::TYPE_BEST_FIELDS)
                ->setQuery($source->getTerm())
                ->setOperator(\Elastica\Query\MultiMatch::OPERATOR_AND)
        );

        $bool->addShould(
            (new \Elastica\Query\MultiMatch())
                ->setFields(['title^1.5', 'content'])
                ->setType(\Elastica\Query\MultiMatch::TYPE_BEST_FIELDS)
                ->setQuery($source->getTerm())
                ->setOperator(\Elastica\Query\MultiMatch::OPERATOR_AND)
                ->setFuzziness(\Elastica\Query\MultiMatch::FUZZINESS_AUTO)
                ->setPrefixLength(2)
        );

        $query = (new BoolQuery())
            ->addMust(
                $bool
            )
            ->addMust(
                (new Type(self::TYPE))
            )
        ;

        return $query;
    }
}
