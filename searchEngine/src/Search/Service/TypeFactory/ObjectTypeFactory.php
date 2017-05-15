<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use Elastica\Query\Type;
use Search\Model\Query;
use Search\Model\User;

class ObjectTypeFactory implements TypeFactoryInterface
{
    const TYPE = "object";

    public function getQuery(Query $source, User $user)
    {
        $query = (new BoolQuery())
            ->addMust(
                (new \Elastica\Query\MultiMatch())
                    ->setFields(['title^2', 'content'])
                    ->setQuery($source->getTerm())
                    ->setFuzziness(\Elastica\Query\MultiMatch::FUZZINESS_AUTO)
            )
            ->addMust(
                (new Type(self::TYPE))
            )
        ;

        if (null !== $user) {
            foreach ($user->getRoles() as $role) {
                $query
                    ->addMustNot(
                        (new  Term())->setTerm('restricted_roles', $role)
                    )
                ;
            }
        }

        return $query;
    }
}
