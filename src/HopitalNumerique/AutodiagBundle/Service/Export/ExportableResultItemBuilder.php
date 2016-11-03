<?php
namespace HopitalNumerique\AutodiagBundle\Service\Export;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Service\Result\ResultItemBuilder;

class ExportableResultItemBuilder extends ResultItemBuilder
{
    public function prepareResponses(Autodiag $autodiag, $syntheses)
    {
        $entryIds = array_map(function (Synthesis $synthesis) {
            return $synthesis->getEntries()->first()->getId();
        }, $syntheses);

        $this->responses = $this->valueRepository->getFullValues(
            $autodiag->getId(),
            $entryIds
        );
    }

//    protected function getResponses(Synthesis $synthesis, Container $container)
//    {
//        $key = $synthesis->getEntries()->first()->getId();
//        if (!array_key_exists($key, $this->responses)) {
//            $this->responses[$key] = $this->valueRepository->getFullValuesByEntry(
//                $synthesis->getAutodiag()->getId(),
//                $synthesis->getEntries()->first()->getId()
//            );
//        }
//
//        foreach ($this->responses[$key] as $response) {
//            if (in_array($container->getId(), $response['container_id'])) {
//                yield $response;
//            }
//        }
//    }
}
