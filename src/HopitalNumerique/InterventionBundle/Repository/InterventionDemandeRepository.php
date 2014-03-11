<?php
/**
 * InterventionDemandeRepository
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;

/**
 * InterventionDemandeRepository
 */
class InterventionDemandeRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getGridDonnees_DemandesNouvelles()
    {
        $demandesInitiales = array();
        
        $requete = $this->_em->createQueryBuilder();
        $requete
            ->select(
                'interventionDemande.id AS id',
                'CONCAT(referent.nom, \' \', referent.prenom, \' (\', referentEtablissement.nom, \', \', referentRegion.libelle, \')\') AS demandeurInformations',
                'CONCAT(ambassadeur.nom, \' \', ambassadeur.prenom, \' (\', ambassadeurRegion.libelle, \')\') AS ambassadeurInformations',
                'objet.id AS objetId',
                'objet.titre AS objetTitre',
                'interventionEtat.libelle AS interventionEtatLibelle',
                'CONCAT(interventionDemande.dateCreation, \'\') AS dateCreationLibelle'
            )
            ->from('HopitalNumeriqueInterventionBundle:InterventionDemande', 'interventionDemande')
                // Référent
                ->innerJoin('interventionDemande.referent', 'referent')
                ->innerJoin('referent.etablissementRattachementSante', 'referentEtablissement')
                ->innerJoin('referent.region', 'referentRegion')
                // Ambassadeur
                ->innerJoin('interventionDemande.ambassadeur', 'ambassadeur')
                ->innerJoin('ambassadeur.region', 'ambassadeurRegion')
                // Objets
                ->leftJoin('interventionDemande.objets', 'objet')
                // État
                ->innerJoin('interventionDemande.interventionEtat', 'interventionEtat')
            ->where('interventionEtat.id = :interventionEtatDemandeInitiale OR interventionEtat.id = :interventionEtatAttenteCmsi')
                ->setparameter('interventionEtatDemandeInitiale', InterventionEtat::getInterventionEtatDemandeInitialeId())
                ->setparameter('interventionEtatAttenteCmsi', InterventionEtat::getInterventionEtatAttenteCmsiId())
        ;
        
        //return $requete->getQUery()->getResult();
        
        $interventionDemandeId = null;
        foreach ($requete->getQUery()->getResult() as $resultat)
        {
            // Demande suivante
            if ($resultat['id'] != $interventionDemandeId)
            {
                $interventionDemandeId = $resultat['id'];
                $demandesInitiales[] = $resultat;
                $demandesInitiales[count($demandesInitiales) - 1]['objetsInformations'] = '';
            }
            if ($resultat['objetId'] != null)
                $demandesInitiales[count($demandesInitiales) - 1]['objetsInformations'] .= '<div>'.$resultat['objetTitre'].'</div>';
        }
        
        return $demandesInitiales;
    }
}
