<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Event\EntryUpdatedEvent;
use HopitalNumerique\AutodiagBundle\Events;
use HopitalNumerique\AutodiagBundle\Form\Type\AutodiagEntry\ValueType;
use HopitalNumerique\AutodiagBundle\Form\Type\SynthesisType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class AutodiagEntryController extends Controller
{
    /**
     * @ParamConverter("autodiag", class="HopitalNumeriqueAutodiagBundle:Autodiag", options={
     *      "repository_method" = "getFullyLoaded"
     * })
     *
     * @param Autodiag $autodiag
     * @param bool     $noLayout
     *
     * @return Response
     */
    public function addAction(Autodiag $autodiag, $noLayout = false)
    {
        $this->get('hopitalnumerique_reference.doctrine.glossaire.parse')->parseAndSaveEntity($autodiag);

        $entry = $this->get('autodiag.entry.session')->get($autodiag)->first();
        if (false !== $entry && $entry->getId()) {
            return $this->redirectToRoute(
                true === $noLayout
                    ? 'hopitalnumerique_autodiag_entry_edit_no_layout'
                    : 'hopitalnumerique_autodiag_entry_edit',
                [
                    'entry' => $entry->getId(),
                ]
            );
        }

        $entry = AutodiagEntry::create($autodiag, $this->getUser());

        return $this->editAction($entry, $noLayout);
    }

    /**
     * @param AutodiagEntry $entry
     *
     * @return RedirectResponse|Response
     */
    public function editAction(AutodiagEntry $entry, $noLayout = false)
    {
        $autodiag = $entry->getSynthesis()->getAutodiag();

        $this->get('hopitalnumerique_reference.doctrine.glossaire.parse')->parseAndSaveEntity($autodiag);

        if (!$this->isGranted('edit', $entry)) {
            return $this->redirectToRoute(
                true === $noLayout
                    ? 'hopitalnumerique_autodiag_entry_add_no_layout'
                    : 'hopitalnumerique_autodiag_entry_add',
                [
                    'autodiag' => $autodiag->getId(),
                ]
            );
        }

        if ($entry->getSynthesis()->isValidated()) {
            return $this->redirectToRoute(
                $noLayout
                    ? 'hopitalnumerique_autodiag_restitution_index_no_layout'
                    : 'hopitalnumerique_autodiag_restitution_index',
                [
                    'synthesis' => $entry->getSynthesis()->getId(),
                ]
            );
        }

        if (false === $autodiag->isPublished()) {
            return $this->redirectToRoute(
                'hopital_numerique_publication_publication_objet',
                ['id' => $this->getParameter('publication_autodiag_unpublished_id')]
            );
        }

        $autodiag = $this->getDoctrine()->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag')
            ->getFullyLoaded($autodiag->getId());

        $forms = [];
        foreach ($autodiag->attributes as $attribute) {
            $builder = $this->get('autodiag.attribute_builder_provider')->getBuilder($attribute->getType());

            $entryValue = $entry->getValues()->filter(function (AutodiagEntry\Value $value) use ($attribute) {
                return $value->getAttribute() == $attribute;
            })->first();

            if (!$entryValue instanceof AutodiagEntry\Value) {
                $entryValue = new AutodiagEntry\Value();
                $entryValue->setAttribute($attribute);
                $entryValue->setEntry($entry);
            }

            $forms[$attribute->getId()] = $builder->getFormHtml($entryValue);
        }

        $synthesisCreateForm = null;
        if (null === $entry->getId()) {
            $synthesisCreateForm = $this->createForm(SynthesisType::class, $entry->getSynthesis(), [
                'action' => $this->generateUrl(
                    $noLayout
                        ? 'hopitalnumerique_autodiag_synthesis_savenew_no_layout'
                        : 'hopitalnumerique_autodiag_synthesis_savenew',
                    [
                        'autodiag' => $autodiag->getId(),
                    ]
                ),
            ])->createView();
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:AutodiagEntry:edit.html.twig', [
            'autodiag' => $autodiag,
            'reason' => $this->get('autodiag.history.reader')->getHistoryByAutodiag($autodiag)[0]->getReason(),
            'entry' => $entry,
            'forms' => $forms,
            'synthesisCreateForm' => $synthesisCreateForm,
            'noLayout' => $noLayout,
        ]);
    }

    /**
     * @param Request            $request
     * @param AutodiagEntry      $entry
     * @param Autodiag\Attribute $attribute
     *
     * @return JsonResponse
     * @ParamConverter("entry")
     * @ParamConverter("attribute")
     */
    public function ajaxAttributeSaveAction(Request $request, AutodiagEntry $entry, Autodiag\Attribute $attribute)
    {
        $this->denyAccessUnlessGranted('edit', $entry);

        $entryValue = $this->getDoctrine()->getRepository('HopitalNumeriqueAutodiagBundle:AutodiagEntry\Value')
            ->findOneBy([
                'entry' => $entry,
                'attribute' => $attribute,
            ]);

        if (null === $entryValue) {
            $entryValue = new AutodiagEntry\Value();
            $entryValue->setAttribute($attribute);
            $entryValue->setEntry($entry);
        }

        $token = $this->get('security.csrf.token_manager')->getToken('entry_value_intention')->getValue();
        if ($request->request->get('_token') !== $token) {
            throw new InvalidCsrfTokenException('Invalid CSRF token');
        }

        $form = $this->createForm(
            ValueType::class,
            $entryValue
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (null === $entryValue->getValue()) {
                $this->getDoctrine()->getManager()->remove($entryValue);
            } else {
                $builder = $this->get('autodiag.attribute_builder_provider')->getBuilder($attribute->getType());
                $entryValue->setValid(!$builder->isEmpty($entryValue->getValue()));
                $this->getDoctrine()->getManager()->persist($entryValue);
            }
            $entry->setUpdatedAt();

            $this->getDoctrine()->getManager()->flush();

            $event = new EntryUpdatedEvent($entry, [$entryValue]);
            $this->get('event_dispatcher')->dispatch(Events::ENTRY_UPDATED, $event);
        }

        return new JsonResponse();
    }

    /**
     * Set all chapter attributes to not concerned value.
     *
     * @param AutodiagEntry              $entry
     * @param Autodiag\Container\Chapter $chapter
     *
     * @ParamConverter("entry")
     * @ParamConverter("chapter")
     *
     * @return JsonResponse
     */
    public function ajaxChapterNotConcernedAction(AutodiagEntry $entry, Autodiag\Container\Chapter $chapter)
    {
        $this->denyAccessUnlessGranted('edit', $entry);

        $values = [];
        foreach ($chapter->getAttributes() as $attribute) {
            $entryValue = $entry->getValues()->filter(function (AutodiagEntry\Value $value) use ($attribute) {
                return $value->getAttribute() == $attribute;
            })->first();

            if (!$entryValue instanceof AutodiagEntry\Value) {
                $entryValue = new AutodiagEntry\Value();
                $entryValue->setAttribute($attribute);
                $entryValue->setEntry($entry);
            }

            $entryValue->setNotConcerned();
            $values[] = $entryValue;
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($entry);
        $manager->flush();

        $event = new EntryUpdatedEvent($entry, $values);
        $this->get('event_dispatcher')->dispatch(Events::ENTRY_UPDATED, $event);

        return new JsonResponse();
    }

    /**
     * Demand for restitution page.
     *
     * @param Request       $request
     * @param AutodiagEntry $entry
     * @param               $target
     *
     * @return Response
     */
    public function restitutionOrValidationDemandAction(Request $request, AutodiagEntry $entry, $target)
    {
        $response = new Response();
        $repo = $this->getDoctrine()->getManager()->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute');
        $autodiag = $entry->getSynthesis()->getAutodiag();

        $filled = 0;
        foreach ($entry->getValues() as $entryValue) {
            $builder = $this->get('autodiag.attribute_builder_provider')->getBuilder(
                $entryValue->getAttribute()->getType()
            );

            if (!$builder->isEmpty($entryValue->getValue())) {
                ++$filled;
            }
        }

        $total = count($repo->getAttributesHavingChapter($autodiag));

        if ($filled == $total) {
            if ($target == 'restitution') {
                $path = $this->generateUrl(
                    true === $request->query->getBoolean('noLayout', false)
                        ? 'hopitalnumerique_autodiag_restitution_index_no_layout'
                        : 'hopitalnumerique_autodiag_restitution_index',
                    [
                        'synthesis' => $entry->getSynthesis()->getId(),
                    ]
                );
            } else {
                $path = $this->generateUrl(
                    true === $request->query->getBoolean('noLayout', false)
                        ? 'hopitalnumerique_autodiag_validation_index_no_layout'
                        : 'hopitalnumerique_autodiag_validation_index',
                    [
                        'synthesis' => $entry->getSynthesis()->getId(),
                    ]
                );
            }
            $response->headers->set('RESTITUTION_REDIRECT', $path);
        } else {
            $response->setContent(
                $this->renderView('@HopitalNumeriqueAutodiag/AutodiagEntry/restitution_demand.html.twig', [
                    'left' => $total - $filled,
                    'autodiag' => $autodiag,
                    'synthesis' => $entry->getSynthesis(),
                    'target' => $target,
                    'noLayout' => true === $request->query->getBoolean('noLayout', false),
                ])
            );
        }

        return $response;
    }
}
