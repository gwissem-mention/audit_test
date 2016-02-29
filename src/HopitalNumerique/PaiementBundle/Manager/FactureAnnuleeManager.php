<?php
namespace HopitalNumerique\PaiementBundle\Manager;

use HopitalNumerique\PaiementBundle\Entity\Facture;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité FactureAnnulee.
 */
class FactureAnnuleeManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\PaiementBundle\Entity\FactureAnnulee';


    /**
     * Crée une facture annulée par rapport à une facture classique.
     *
     * @param \HopitalNumerique\PaiementBundle\Entity\Facture $facture Facture originale
     * @return \HopitalNumerique\PaiementBundle\Entity\FactureAnnulee Nouvelle facture annulée
     */
    public function createByFacture(Facture $facture)
    {
        $factureAnnulee = $this->createEmpty();

        $factureAnnulee->setFacture($facture);
        $factureAnnulee->setInterventions($facture->getInterventions());
        $factureAnnulee->setFormations($facture->getFormations());

        return $factureAnnulee;
    }
}
