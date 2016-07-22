<?php

namespace HopitalNumerique\AutodiagBundle\Service\Algorithm;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

class Score
{
    public function getScore(Synthesis $synthesis, Container $container)
    {
        foreach ($synthesis->getEntries() as $entry) {
            /** @var AutodiagEntry $entry */

            // Récupérer les réponses éligibles au calcul de l'algo dont la valeur est !== -1
            $values = $entry->getValues();
            foreach ($values as $value) {
                // get score via AttributeBuilder
                //      => créer une méthode "getScore" dans les attributeBilder pour gérer criticité et risque ?
                dump($value);
            }
        }
        die;
    }
}
