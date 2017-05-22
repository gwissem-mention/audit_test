<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\Term;
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
                    ->setFieldPrefixLength('content', 2)
                    ->setFieldOperator('content', \Elastica\Query\Common::OPERATOR_AND)
            )
            ->addMust(new Type(self::TYPE))
        ;

        if (null !== $user) {
            $roleQuery = new BoolQuery();

            foreach ($user->getRoles() as $role) {
                $roleQuery
                    ->addShould(
                        (new  Term())->setTerm('authorised_roles', $role)
                    )
                ;
            }

            $query->addMust($roleQuery);
        }

        return $query;
    }
}
