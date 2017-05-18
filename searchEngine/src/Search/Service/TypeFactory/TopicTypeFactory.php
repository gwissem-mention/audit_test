<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use Elastica\Query\Type;
use Search\Model\Query;
use Search\Model\User;

class TopicTypeFactory implements TypeFactoryInterface
{
    const TYPE = "forum_topic";

    public function getQuery(Query $source, User $user)
    {
        $query = (new BoolQuery())
            ->addMust(
                (new \Elastica\Query\MultiMatch())
                    ->setFields(['title', 'forumName'])
                    ->setQuery($source->getTerm())
                    ->setFuzziness(\Elastica\Query\MultiMatch::FUZZINESS_AUTO)
                    ->setPrefixLength(2)
            )
            ->addMust(
                (new Type(self::TYPE))
            )
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
