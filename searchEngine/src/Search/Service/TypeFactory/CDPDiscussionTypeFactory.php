<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\BoolQuery;
use Elastica\Query\Type;
use Search\Model\Query;
use Search\Model\User;

class CDPDiscussionTypeFactory extends ConfigurableFactory
{
    const TYPE = "cdp_discussion";

    public function getQuery(Query $source, User $user)
    {
        $bool = new BoolQuery();

        if ($this->config->get('query.exact.enabled', self::TYPE)) {
            $subQuery = (new \Elastica\Query\MultiMatch())
                ->setFields([
                    sprintf('title.exact^%f', $this->config->get('boost.title')),
                    'content.exact',
                    'description.exact',
                ])
                ->setType(\Elastica\Query\MultiMatch::TYPE_BEST_FIELDS)
                ->setQuery($source->getTerm())
                ->setParam('boost', $this->config->get('query.exact.boost', self::TYPE))
            ;

            $this->addFuzzinessToMultimatch($subQuery, 'exact', self::TYPE);

            $bool->addShould($subQuery);
        }

        if ($this->config->get('query.similar.enabled', self::TYPE)) {
            $subQuery = (new \Elastica\Query\MultiMatch())
                ->setFields([
                    sprintf('title.exact^%f', $this->config->get('boost.title')),
                    'content.exact',
                    'description.exact',
                ])
                ->setType(\Elastica\Query\MultiMatch::TYPE_BEST_FIELDS)
                ->setQuery($source->getTerm())
            ;

            $this->addFuzzinessToMultimatch($subQuery, 'similar', self::TYPE);

            $bool->addShould($subQuery);
        }

        if ($this->config->get('query.suggestion.enabled', self::TYPE)) {
            $subQuery =
                (new \Elastica\Query\MultiMatch())
                    ->setFields([
                        sprintf('title^%f', $this->config->get('boost.title')),
                        'content',
                        'description',
                    ])
                    ->setType(\Elastica\Query\MultiMatch::TYPE_BEST_FIELDS)
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

        return $query;
    }
}
