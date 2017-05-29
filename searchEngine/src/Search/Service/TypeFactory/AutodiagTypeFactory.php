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
            (new Match())
                ->setFieldQuery('title.exact', $source->getTerm())
                ->setFieldOperator('title.exact', \Elastica\Query\Common::OPERATOR_AND)
        );

        $bool->addShould(
            (new Match())
                ->setFieldQuery('title', $source->getTerm())
                ->setFieldFuzziness('title', 'AUTO')
                ->setFieldPrefixLength('title', 2)
                ->setFieldOperator('title', \Elastica\Query\Common::OPERATOR_AND)
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
