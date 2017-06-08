<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Form\Type\SynthesisType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SynthesisController extends Controller
{
    public function saveNewAction(Request $request, Autodiag $autodiag, $noLayout = false)
    {
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
                    'entry' => $synthesis->getEntries()->first()->getId(),
                ]
            );
        }

        return $this->createAccessDeniedException();
    }

    public function scorePollingAction(Request $request)
    {
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

        return null;
    }

    /**
     * @param Request   $request
     * @param Synthesis $synthesis
     *
     * @return JsonResponse
     */
    public function changeNameAction(Request $request, Synthesis $synthesis)
    {
        if (!$this->isGranted('edit', $synthesis)) {
             throw new AccessDeniedException();
        }

        $form = $this->createForm(SynthesisType::class, $synthesis, [
            'csrf_protection' => false,
        ]);

        $form->submit([
            'name' => $request->request->get('text'),
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('doctrine.orm.entity_manager')->flush($synthesis);
        }

        return new JsonResponse();
    }
}
