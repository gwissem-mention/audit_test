<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Form\Type\SynthesisType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SynthesisController extends Controller
{
    public function saveNewAction(Request $request, Autodiag $autodiag, $noLayout = false)
    {
        if (!in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '109.26.138.14'))) {
            die('Les autodiagnostics sont en maintenance');
        }

        $entry = AutodiagEntry::create($autodiag, $this->getUser());
        $synthesis = $entry->getSynthesis();

        $form = $this->createForm(SynthesisType::class, $synthesis);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entry->setName($synthesis->getName());
            $this->get('doctrine.orm.entity_manager')->persist($synthesis);
            $this->get('doctrine.orm.entity_manager')->flush();

            if (null === $entry->getUser()) {
                $this->get('autodiag.entry.session')->add($entry);
            }

            return $this->redirectToRoute(
                $noLayout ? 'hopitalnumerique_autodiag_entry_edit_no_layout' : 'hopitalnumerique_autodiag_entry_edit',
                [
                    'entry' => $synthesis->getEntries()->first()->getId()
                ]
            );
        }

        return $this->createAccessDeniedException();
    }


    public function scorePollingAction(Request $request)
    {
        if (!in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '109.26.138.14'))) {
            die('Les autodiagnostics sont en maintenance');
        }

        $syntheses = $request->query->get('syntheses');

        $start = time();
        $limit = $start + 20;

        while (true) {
            $result = $this->get('autodiag.repository.synthesis')->getScorePolling($syntheses);

            if (count($result) !== count($syntheses)) {
                return new JsonResponse(
                    $result
                );
            }

            if (time() >= $limit) {
                return new JsonResponse(
                    $syntheses
                );
            }

            usleep(1000);
        }
    }
}
