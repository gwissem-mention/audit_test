<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use Elastica\Query\Type;
use Search\Model\Query;
use Search\Model\User;

class TopicTypeFactory extends ConfigurableFactory
{
    const TYPE = "forum_topic";

    public function getQuery(Query $source, User $user)
    {
        $bool = new BoolQuery();

        if ($this->config->get('query.exact.enabled', self::TYPE)) {
            $subQuery = (new \Elastica\Query\MultiMatch())
                ->setFields([
                    sprintf('title.exact^%f', $this->config->get('boost.title')),
                    'forumName.exact',
                ])
                ->setQuery($source->getTerm())
                ->setOperator(\Elastica\Query\MultiMatch::OPERATOR_AND)
                ->setParam('boost', $this->config->get('query.exact.boost', self::TYPE))
            ;

            $this->addFuzzinessToMultimatch($subQuery, 'exact', self::TYPE);

            $bool->addShould($subQuery);
        }


        if ($this->config->get('query.similar.enabled', self::TYPE)) {
            $subQuery = (new \Elastica\Query\MultiMatch())
                ->setFields([
                    sprintf('title.exact^%f', $this->config->get('boost.title')),
                    'forumName.exact',
                ])
                ->setQuery($source->getTerm())
                ->setOperator(\Elastica\Query\MultiMatch::OPERATOR_AND)
            ;

            $this->addFuzzinessToMultimatch($subQuery, 'similar', self::TYPE);

            $bool->addShould($subQuery);
        }


        if ($this->config->get('query.suggestion.enabled', self::TYPE)) {
            $subQuery = (new \Elastica\Query\MultiMatch())
                ->setFields([
                    sprintf('title^%f', $this->config->get('boost.title')),
                    'forumName',
                ])
                ->setQuery($source->getTerm())
                ->setOperator(\Elastica\Query\MultiMatch::OPERATOR_AND)
            ;

            $this->addFuzzinessToMultimatch($subQuery, 'suggestion', self::TYPE);

            $bool->addShould($subQuery);
        }


        $query = (new BoolQuery())
            ->addMust(
                $bool
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
