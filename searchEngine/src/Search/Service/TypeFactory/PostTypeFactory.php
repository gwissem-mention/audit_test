<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\Term;
use Elastica\Query\Type;
use Search\Model\Query;
use Search\Model\User;

class PostTypeFactory extends ConfigurableFactory
{
    const TYPE = "forum_post";

    public function getQuery(Query $source, User $user)
    {
        $bool = new BoolQuery();

        if ($this->config->get('query.exact.enabled', self::TYPE)) {
            $subQuery = (new Match())
                ->setFieldQuery('content.exact', $source->getTerm())
                ->setFieldBoost('content.exact', $this->config->get('query.exact.boost', self::TYPE))
                ->setFieldOperator('content.exact', \Elastica\Query\Common::OPERATOR_AND)
            ;

            $this->addFuzzinessToMatch($subQuery, 'content.exact', 'exact', self::TYPE);

            $bool->addShould($subQuery);
        }

        if ($this->config->get('query.similar.enabled', self::TYPE)) {
            $subQuery = (new Match())
                ->setFieldQuery('content.exact', $source->getTerm())
                ->setFieldOperator('content.exact', \Elastica\Query\Common::OPERATOR_AND)
            ;

            $this->addFuzzinessToMatch($subQuery, 'content.exact', 'similar', self::TYPE);

            $bool->addShould($subQuery);
        }

        if ($this->config->get('query.suggestion.enabled', self::TYPE)) {
            $subQuery =
                (new Match())
                    ->setFieldQuery('content', $source->getTerm())
                    ->setFieldOperator('content', \Elastica\Query\Common::OPERATOR_AND)
            ;

            $this->addFuzzinessToMatch($subQuery, 'content', 'suggestion', self::TYPE);

            $bool->addShould($subQuery);
        }

        $query = (new BoolQuery())
            ->addMust(
                $bool
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
