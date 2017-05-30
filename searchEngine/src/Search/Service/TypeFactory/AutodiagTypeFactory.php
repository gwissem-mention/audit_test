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
        $bool = new BoolQuery();
        $bool->addShould(
            (new \Elastica\Query\MultiMatch())
                ->setFields(['title.exact^1.5', 'chapter_label.exact'])
                ->setType(\Elastica\Query\MultiMatch::TYPE_BEST_FIELDS)
                ->setQuery($source->getTerm())
                ->setOperator(\Elastica\Query\MultiMatch::OPERATOR_AND)
                ->setFuzziness(1)
                ->setPrefixLength(2)
                ->setMaxExpansions(5)
        );

        $bool->addShould(
            (new \Elastica\Query\MultiMatch())
                ->setFields(['title^1.5', 'chapter_label'])
                ->setType(\Elastica\Query\MultiMatch::TYPE_BEST_FIELDS)
                ->setQuery($source->getTerm())
                ->setOperator(\Elastica\Query\MultiMatch::OPERATOR_AND)
                ->setFuzziness(\Elastica\Query\MultiMatch::FUZZINESS_AUTO)
                ->setPrefixLength(2)
                ->setMaxExpansions(5)
        );

        $query = (new BoolQuery())
            ->addMust(
                $bool
            )
            ->addMust(new Type(self::TYPE))
        ;

        return $query;
    }
}
