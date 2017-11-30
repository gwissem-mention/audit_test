<?php

namespace Search\Service;

use Search\Model\Query;

/**
 * Class SearchStatsRepository
 */
class SearchStatsRepository
{
    /**
     * @var \PDO
     */
    protected $connexion;

    /**
     * SearchStatsRepository constructor.
     *
     * @param \PDO $connexion
     */
    public function __construct(\PDO $connexion)
    {
        $this->connexion = $connexion;
    }

    /**
     * @param Query $query
     * @param $token
     * @param $nbResults
     * @param $isProduction
     */
    public function insertSearch(Query $query, $token, $nbResults, $isProduction)
    {
        $index        = $query->getIndex();
        $term         = $query->getTerm();
        $size         = $query->getSize();
        $from         = $query->getFrom();
        $isProduction = $isProduction ? 1 : 0;

        $sql = $this->connexion->prepare(
            'INSERT INTO hn_search_stats(search_token, user_id, search_date, search_results, search_index, search_term, search_size, search_from, search_is_production)
            (SELECT token, user_id, NOW(), :nbResults, :index, :term, :size, :from, :isProduction FROM core_user_token WHERE token = :token);'
        );
        $sql->bindParam(':token', $token);
        $sql->bindParam(':nbResults', $nbResults);
        $sql->bindParam(':index', $index);
        $sql->bindParam(':term', $term);
        $sql->bindParam(':size', $size);
        $sql->bindParam(':from', $from);
        $sql->bindParam(':isProduction', $isProduction);
        $sql->execute();
    }
}
