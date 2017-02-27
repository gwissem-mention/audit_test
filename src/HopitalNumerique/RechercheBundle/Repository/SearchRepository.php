<?php

namespace HopitalNumerique\RechercheBundle\Repository;

use Elastica\Query;

class SearchRepository
{
    public function search($term)
    {
        $query = new Query\BoolQuery();
    }
}
