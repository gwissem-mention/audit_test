<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Form\Type\AutodiagEntryType;
use HopitalNumerique\AutodiagBundle\Form\Type\AutodiagEntry\ValueType;
use HopitalNumerique\AutodiagBundle\Form\Type\SynthesisType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AutodiagEntryController extends Controller
{
    public function addAction(Autodiag $autodiag)
    {
        $synthesis = Synthesis::create($autodiag);
        $form = $this->createForm(SynthesisType::class, $synthesis, [
            'action' => $this->generateUrl('hopitalnumerique_autodiag_synthesis_savenew', [
                'autodiag' => $autodiag->getId()
            ])
        ]);

        return $this->render('HopitalNumeriqueAutodiagBundle:AutodiagEntry:add.html.twig', [
            'form' => $form->createView(),
            'autodiag' => $autodiag,
        ]);
    }

    public function editAction(AutodiagEntry $entry)
    {
        $autodiag = $entry->getSynthesis()->getAutodiag();
        $autodiag = $this->getDoctrine()->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag')
            ->getFullyLoaded($autodiag);

        $forms = [];
        foreach ($autodiag->attributes as $attribute) {
            $entryValue = new AutodiagEntry\Value();
            $entryValue->setAttribute($attribute);
            $entryValue->setEntry($entry);

            $forms[$attribute->getId()] = $this->createForm(
                ValueType::class,
                $entryValue
            )->createView();
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:AutodiagEntry:edit.html.twig', [
            'autodiag' => $autodiag,
            'entry' => $entry,
            'forms' => $forms,
        ]);
    }
}
