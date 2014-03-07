<?php
/**
 * Manager pour les établissements utilisés dans les formulaires des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manager pour les établissements utilisés dans les formulaires des demandes d'intervention.
 */
class EtablissementManager
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     */
    private $container;
    
    /**
     * Constructeur du manager des établissements pour les formulaires utilisateurs.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Retourne la liste jsonifiée des établissements regroupés par type d'organisme.
     *
     * @param array $criteres Le filtre à appliquer sur la liste
     * @return string La liste des établissements regroupés par type d'organisme jsonifiée
     */
    public function jsonEtablissementsRegroupesParTypeOrganisme(array $criteres)
    {
        $etablissementsRegroupesParTypeOrganisme = $this->container->get('hopitalnumerique_etablissement.manager.etablissement')->getEtablissementsRegroupesParTypeOrganisme($criteres);
        $etablissementsListeFormatee = array();
        
        foreach ($etablissementsRegroupesParTypeOrganisme as $etablissementsRegroupes)
        {
            $etablissementsListeFormatee[] = array(
                'typeOrganisme' => array(
                    'id' => ($etablissementsRegroupes['typeOrganisme'] != null ? $etablissementsRegroupes['typeOrganisme']->getId() : '0'),
                    'libelle' => ($etablissementsRegroupes['typeOrganisme'] != null ? $etablissementsRegroupes['typeOrganisme']->getLibelle() : '')
                ),
                'etablissements' => array()
            );
            foreach ($etablissementsRegroupes['etablissements'] as $etablissement)
            {
                $etablissementsListeFormatee[count($etablissementsListeFormatee) - 1]['etablissements'][] = array(
                    'id' => $etablissement->getId(),
                    'nom' => $etablissement->getNom()
                );
            }
        }
        
        return json_encode($etablissementsListeFormatee);
    }
}
