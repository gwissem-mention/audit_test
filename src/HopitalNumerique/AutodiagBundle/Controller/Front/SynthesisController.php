<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Form\Type\SynthesisType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SynthesisController extends Controller
{
    public function saveNewAction(Request $request, Autodiag $autodiag)
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

            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_edit', [
                'entry' => $synthesis->getEntries()->first()->getId()
            ]);
        }

        return $this->createAccessDeniedException();
    }
}
