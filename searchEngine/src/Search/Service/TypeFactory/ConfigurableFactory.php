<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\Match;
use Elastica\Query\MultiMatch;
use Search\Service\QueryConfigurator;

abstract class ConfigurableFactory implements TypeFactoryInterface
{
    /**
     * @var QueryConfigurator
     */
    protected $config;

    /**
     * ConfigurableFactory constructor.
     * @param QueryConfigurator $config
     */
    public function __construct(QueryConfigurator $config)
    {
        $this->config = $config;
    }

    protected function addFuzzinessToMultimatch(MultiMatch $query, $queryType, $objectType)
    {
        if ($this->config->get("query.$queryType.fuzziness.enabled", $objectType)) {
            $query
                ->setFuzziness($this->config->get("query.$queryType.fuzziness.value", $objectType))
                ->setPrefixLength($this->config->get("query.$queryType.fuzziness.prefix", $objectType))
                ->setMaxExpansions($this->config->get("query.$queryType.fuzziness.expansion", $objectType))
            ;
        }

        return $query;
    }

    protected function addFuzzinessToMatch(Match $query, $field, $queryType, $objectType)
    {
        if ($this->config->get("query.$queryType.fuzziness.enabled", $objectType)) {
            $query
                ->setFieldFuzziness($field, $this->config->get("query.$queryType.fuzziness.value", $objectType))
                ->setFieldPrefixLength($field, $this->config->get("query.$queryType.fuzziness.prefix", $objectType))
                ->setFieldMaxExpansions($field, $this->config->get("query.$queryType.fuzziness.expansion", $objectType));
        }

        return $query;
    }
}
