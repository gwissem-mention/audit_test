<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Form\Type\AutodiagEntry\ValueType;
use HopitalNumerique\AutodiagBundle\Form\Type\SynthesisType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AutodiagEntryController extends Controller
{
    /**
     * @ParamConverter("autodiag", class="HopitalNumeriqueAutodiagBundle:Autodiag", options={
     *      "repository_method" = "getFullyLoaded"
     * })
     *
     * @param Autodiag $autodiag
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Autodiag $autodiag)
    {
        $entry = $this->get('autodiag.entry.session')->get($autodiag)->first();
        if (false !== $entry && $entry->getId()) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_edit', [
                'entry' => $entry->getId()
            ]);
        }

        $entry = AutodiagEntry::create($autodiag, $this->getUser());
        return $this->editAction($entry);
    }

    /**
     * @param AutodiagEntry $entry
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(AutodiagEntry $entry)
    {
        $autodiag = $entry->getSynthesis()->getAutodiag();

        if (!$this->isGranted('edit', $entry)) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_entry_add', [
                'autodiag' => $autodiag->getId()
            ]);
        }

        // Assigne l'utilisateur courrant à l'autodiag entry courrant
        // On supprime l'entry de la session
        if ($this->getUser() && null === $entry->getUser()) {
            $manager = $this->getDoctrine()->getManager();
            $entry->setUser($this->getUser());
            $manager->persist($entry);
            $manager->flush();

            $this->get('autodiag.entry.session')->remove($entry);
        }

        $autodiag = $this->getDoctrine()->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag')
            ->getFullyLoaded($autodiag->getId());

        $forms = [];
        foreach ($autodiag->attributes as $attribute) {
            $entryValue = $entry->getValues()->filter(function (AutodiagEntry\Value $value) use ($attribute) {
                return $value->getAttribute() == $attribute;
            })->first();

            if (!$entryValue instanceof AutodiagEntry\Value) {
                $entryValue = new AutodiagEntry\Value();
                $entryValue->setAttribute($attribute);
                $entryValue->setEntry($entry);
            }

            $forms[$attribute->getId()] = $this->createForm(
                ValueType::class,
                $entryValue,
                [
                    'action' => $entry->getId() !== null
                        ? $this->generateUrl('hopitalnumerique_autodiag_entry_attribute_save', [
                            'entry' => $entry->getId(),
                            'attribute' => $attribute->getId(),
                        ])
                        : null
                ]
            )->createView();
        }

        $synthesisCreateForm = null;
        if (null === $entry->getId()) {
            $synthesisCreateForm = $this->createForm(SynthesisType::class, $entry->getSynthesis(), [
                'action' => $this->generateUrl('hopitalnumerique_autodiag_synthesis_savenew', [
                    'autodiag' => $autodiag->getId()
                ])
            ])->createView();
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:AutodiagEntry:edit.html.twig', [
            'autodiag' => $autodiag,
            'entry' => $entry,
            'forms' => $forms,
            'synthesisCreateForm' => $synthesisCreateForm,
        ]);
    }

    /**
     * @param AutodiagEntry $entry
     * @param Autodiag\Attribute $attribute
     *
     * @ParamConverter("entry")
     * @ParamConverter("attribute")
     * @return JsonResponse
     */
    public function ajaxAttributeSaveAction(Request $request, AutodiagEntry $entry, Autodiag\Attribute $attribute)
    {
        $this->denyAccessUnlessGranted('edit', $entry);

        $entryValue = $this->getDoctrine()->getRepository('HopitalNumeriqueAutodiagBundle:AutodiagEntry\Value')
            ->findOneBy([
                'entry' => $entry,
                'attribute' => $attribute
            ]);

        if (null === $entryValue) {
            $entryValue = new AutodiagEntry\Value();
            $entryValue->setAttribute($attribute);
            $entryValue->setEntry($entry);
        }

        $form = $this->createForm(
            ValueType::class,
            $entryValue
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($entryValue);
            $this->getDoctrine()->getManager()->flush();
        }

        return new JsonResponse();
    }

    public function ajaxChapterNotConcernedAction(AutodiagEntry $entry, Autodiag\Container\Chapter $chapter)
    {
        foreach ($chapter->getAttributes() as $attribute) {
            $entryValue = $entry->getValues()->filter(function (AutodiagEntry\Value $value) use ($attribute) {
                return $value->getAttribute() == $attribute;
            })->first();

            if (!$entryValue instanceof AutodiagEntry\Value) {
                $entryValue = new AutodiagEntry\Value();
                $entryValue->setAttribute($attribute);
                $entryValue->setEntry($entry);
                $entryValue->setValue(-1);
            }
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($entry);
        $manager->flush();

        return new JsonResponse();
    }
}
