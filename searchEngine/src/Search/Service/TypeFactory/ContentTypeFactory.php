<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
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
                    ->setOperator(\Elastica\Query\MultiMatch::OPERATOR_AND)
                    ->setFuzziness(1)
                    ->setPrefixLength(2)
                    ->setMaxExpansions(5)
            )
            ->addMust(
                (new Type(self::TYPE))
            )
        ;

        if (null !== $user) {
            foreach ($user->getRoles() as $role) {
                $query
                    ->addMustNot(
                        (new \Elastica\Query\Nested())
                            ->setPath('parent')
                            ->setQuery(
                                (new  Term())->setTerm('parent.restricted_roles', $role)
                            )
                    )
                ;
            }
        }

        return $query;
    }
}
