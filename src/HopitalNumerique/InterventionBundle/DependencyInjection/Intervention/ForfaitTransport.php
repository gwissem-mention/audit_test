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
     * @var DistanceMatrix DistanceMatrix
     */
    private $distanceMatrix;

    /**
     * @var ForfaitTransportManager ForfaitTransportManager
     */
    private $forfaitTransportManager;

    /**
     * Constructeur.
     *
     * @param DistanceMatrix          $distanceMatrix
     * @param ForfaitTransportManager $forfaitTransportManager
     */
    public function __construct(DistanceMatrix $distanceMatrix, ForfaitTransportManager $forfaitTransportManager)
    {
        $this->distanceMatrix = $distanceMatrix;
        $this->forfaitTransportManager = $forfaitTransportManager;
    }

    /**
     * Retourne la distance (en km) entre 2 codes postaux.
     *
     * @param string $codePostalDepart
     * @param string $codePostalArrivee
     *
     * @return int
     * @throws \Exception
     */
    private function getDistanceBetweenCodesPostaux($codePostalDepart, $codePostalArrivee)
    {
        $distanceResponse = $this->distanceMatrix->process(
            [$codePostalDepart . ' France'],
            [$codePostalArrivee . ' France']
        );
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
     * @param int $distance
     *
     * @return int
     * @throws \Exception
     */
    private function getCoutForDistance($distance)
    {
        $forfaitTransport = $this->forfaitTransportManager->getForDistance($distance);
        if (null === $forfaitTransport) {
            throw new \Exception('Forfait transport pour "' . $distance . '" km non trouvé.');
        }

        return $forfaitTransport->getCout();
    }

    /**
     * Retourne le coût de transport entre 2 établissements.
     *
     * @param Etablissement $etablissement1
     * @param Etablissement $etablissement2
     *
     * @return int
     */
    public function getCoutForDistanceBetweenEtablissements(
        Etablissement $etablissement1,
        Etablissement $etablissement2
    ) {
        $distance = $this->getDistanceBetweenCodesPostaux(
            $etablissement1->getCodepostal(),
            $etablissement2->getCodepostal()
        ) * 2;

        return $this->getCoutForDistance($distance);
    }
}
