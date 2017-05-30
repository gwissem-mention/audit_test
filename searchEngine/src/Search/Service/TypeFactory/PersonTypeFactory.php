<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\Term;
use Elastica\Query\Type;
use Search\Model\Query;
use Search\Model\User;

class PersonTypeFactory implements TypeFactoryInterface
{
    const TYPE = "person";

    public function getQuery(Query $source, User $user)
    {
        $bool = new BoolQuery();
        $bool
            ->addShould(
                (new \Elastica\Query\MultiMatch())
                    ->setFields(['firstname', 'lastname'])
                    ->setType(\Elastica\Query\MultiMatch::TYPE_CROSS_FIELDS)
                    ->setTieBreaker(1.0)
                    ->setQuery($source->getTerm())
                    ->setParam('boost', 5)
            )
            ->addShould(
                (new BoolQuery())
                    ->addShould(
                        (new Match())
                            ->setFieldQuery('biography.exact', $source->getTerm())
                    )
                    ->addShould(
                        (new Match())
                            ->setFieldQuery('biography', $source->getTerm())
                            ->setFieldFuzziness('biography', 'AUTO')
                            ->setFieldPrefixLength('biography', 2)
                            ->setFieldOperator('biography', \Elastica\Query\Common::OPERATOR_AND)
                    )
            )
            ->addShould(
                (new \Elastica\Query\MultiMatch())
                    ->setFields(['phone', 'cellphone'])
                    ->setType(\Elastica\Query\MultiMatch::TYPE_BEST_FIELDS)
                    ->setQuery($source->getTerm())
                    ->setParam('boost', 20)
            )
        ;

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
