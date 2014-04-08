<?php
/**
 * Manager pour les établissements utilisés dans les formulaires des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager\Form;

use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * Manager pour les établissements utilisés dans les formulaires des demandes d'intervention.
 */
class EtablissementManager
{
    /**
     * @var \HopitalNumerique\EtablissementBundle\Manager\EtablissementManager Manager de Etablissement
     */
    private $etablissementManager;

    /**
     * Constructeur du manager des établissements pour les formulaires utilisateurs.
     *
     * @param \HopitalNumerique\EtablissementBundle\Manager\EtablissementManager $etablissementManager Manager de Etablissement
     * @return void
     */
    public function __construct(\HopitalNumerique\EtablissementBundle\Manager\EtablissementManager $etablissementManager)
    {
        $this->etablissementManager = $etablissementManager;
    }

    /**
     * Retourne la liste des établissements pour les listes de formulaire.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference|null $region Région des établissements à récupérer
     * @return array Liste des établissements pour les listes de formulaire
     */
    public function getEtablissementsChoices(Reference $region = null)
    {
        $etablissementsFiltre = array();
        if ($region != null)
            $etablissementsFiltre['region'] = $region;

        $etablissements = $this->etablissementManager->findAll($etablissementsFiltre);

        return $etablissements;
    }

    /**
     * Retourne la liste jsonifiée des établissements regroupés par type d'organisme.
     *
     * @param array $criteres Le filtre à appliquer sur la liste
     * @return string La liste des établissements regroupés par type d'organisme jsonifiée
     */
    public function jsonEtablissementsRegroupesParTypeOrganisme(array $criteres)
    {
        $etablissementsRegroupesParTypeOrganisme = $this->etablissementManager->getEtablissementsRegroupesParTypeOrganisme($criteres);
        $etablissementsListeFormatee = array();

        foreach ($etablissementsRegroupesParTypeOrganisme as $etablissementsRegroupes)
        {
            $etablissementsListeFormatee[] = array(
                'typeOrganisme' => array(
                'id' => ($etablissementsRegroupes['typeOrganisme'] != null ? $etablissementsRegroupes['typeOrganisme']->getId() : '0'),
                'libelle' => ($etablissementsRegroupes['typeOrganisme'] != null ? $etablissementsRegroupes['typeOrganisme']->getLibelle() : '')), 'etablissements' => array()
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
    
    /**
     * Retourne la liste jsonifiée des établissements.
     *
     * @param array $criteres Le filtre à appliquer sur la liste
     * @return string La liste des établissements
     */
    public function jsonEtablissements(array $criteres)
    {
        $etablissements = $this->etablissementManager->findBy($criteres);
        $etablissementsListeFormatee = array();
    
        foreach ($etablissements as $etablissement)
        {
            $etablissementsListeFormatee[] = array(
                'id' => $etablissement->getId(),
                'appellation' => $etablissement->getAppellation()
            );
        }
    
        return json_encode($etablissementsListeFormatee);
    }
}
