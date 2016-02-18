<?php
namespace HopitalNumerique\InterventionBundle\DependencyInjection\Intervention;

use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\InterventionBundle\Manager\Intervention\ForfaitTransportManager;
use Ivory\GoogleMap\Services\DistanceMatrix\DistanceMatrix;

/**
 * Service gérant les forfaits de transport.
 */
class ForfaitTransport
{
    /**
     * @var \Ivory\GoogleMap\Services\DistanceMatrix\DistanceMatrix DistanceMatrix
     */
    private $distanceMatrix;

    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\Intervention\ForfaitTransportManager ForfaitTransportManager
     */
    private $forfaitTransportManager;


    /**
     * Constructeur.
     */
    public function __construct(DistanceMatrix $distanceMatrix, ForfaitTransportManager $forfaitTransportManager)
    {
        $this->distanceMatrix = $distanceMatrix;
        $this->forfaitTransportManager = $forfaitTransportManager;
    }


    /**
     * Retourne la distance (en km) entre 2 codes postaux.
     *
     * @param string $codePostalDepart  Code postal de départ
     * @param string $codePostalArrivee Code postal d'arrivée
     * @return integer Distance
     */
    private function getDistanceBetweenCodesPostaux($codePostalDepart, $codePostalArrivee)
    {
        $distanceResponse = $this->distanceMatrix->process(array($codePostalDepart.' France'), array($codePostalArrivee.' France'));
        if ('OK' == strtoupper($distanceResponse->getStatus())) {
            $distances = $distanceResponse->getRows();
            if (count($distances) > 0 && count($distances[0]->getElements()) > 0) {
                $distanceElements = $distances[0]->getElements();
                return intval(floor($distanceElements[0]->getDistance()->getValue() / 1000));
            }
        }

        throw new \Exception('Erreur Googlemaps API.');
    }

    /**
     * Retourne le coût selon la distance.
     *
     * @param integer $distance Distance en km
     * @return integer Coût de l'intervention
     */
    private function getCoutForDistance($distance)
    {
        $forfaitTransport = $this->forfaitTransportManager->getForDistance($distance);
        if (null === $forfaitTransport) {
            throw new \Exception('Forfait transport pour "'.$distance.'" km non trouvé.');
        }

        return $forfaitTransport->getCout();
    }

    /**
     * Retourne le coût de transport entre 2 établissements.
     *
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissement1 Établissement 1
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissement2 Établissement 2
     */
    public function getCoutForDistanceBetweenEtablissements(Etablissement $etablissement1, Etablissement $etablissement2)
    {
        $distance = $this->getDistanceBetweenCodesPostaux($etablissement1->getCodepostal(), $etablissement2->getCodepostal()) * 2;
        return $this->getCoutForDistance($distance);
    }
}
