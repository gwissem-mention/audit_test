<?php
namespace HopitalNumerique\PublicationBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SuggestionRepository extends EntityRepository
{
    public function getDatasForGrid()
    {
        $qb = $this->createQueryBuilder('suggestion');
        $qb
            ->select(
                "suggestion.id id",
                "suggestion.creationDate",
                "suggestion.title",
                "GROUP_CONCAT(domains.nom SEPARATOR ' - ') domainsName",
                "state.libelle stateLabel",
                "suggestion.stateChangeDate",
                "CONCAT(CONCAT(stateChangeAuthor.prenom, ' '), stateChangeAuthor.nom) stateChangeAuthorName"
            )
            ->leftJoin('suggestion.domains', 'domains')
            ->leftJoin('suggestion.state', 'state')
            ->leftJoin('suggestion.stateChangeAuthor', 'stateChangeAuthor')
            ->addOrderBy('suggestion.creationDate', 'DESC')
            ->groupBy('suggestion.id')
        ;

        return $qb->getQuery()->getArrayResult();
    }
}
