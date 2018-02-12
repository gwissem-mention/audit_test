<?php

namespace Search\Service;

use Search\Model\Query;
use Search\Model\Query\Filter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Transform a request in Query object
 *
 * @package Search
 */
class RequestToQueryTransformer
{
    /**
     * Do Request to Query transformation
     *
     * @param Request $request
     *
     * @return Query
     */
    public function transform(Request $request)
    {
        $requestQuery = $request->query;
        $query = new Query($requestQuery->get('index'));

        $query
            ->setTerm($requestQuery->get('term'))
            ->setFrom((int) $requestQuery->get('from', 0))
            ->setSize((int) $requestQuery->get('size', 10))
            ->setSource($requestQuery->get('source'))
        ;

        foreach ($this->getFilters($request) as $filter) {
            $query->addFilter($filter);
        }

        return $query;
    }

    /**
     * @param Request $request
     *
     * @return \Generator
     */
    protected function getFilters(Request $request)
    {
        $requestQuery = $request->query;
        foreach ($requestQuery->get('filters', []) as $filter) {
            yield new Filter(
                $filter['field'],
                $filter['value']
            );
        }
    }
}
