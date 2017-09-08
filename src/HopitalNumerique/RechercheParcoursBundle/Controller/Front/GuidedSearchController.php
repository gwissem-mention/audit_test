<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller\Front;

use Nodevo\MailBundle\Entity\Mail;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nodevo\MailBundle\Form\Type\RecommandationType;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Form\Type\Risk\AddRiskType;
use HopitalNumerique\RechercheParcoursBundle\Form\Type\Risk\ShowRiskType;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\IncludeRiskCommand;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\AddPrivateRiskCommand;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchConfigPublicationType;
use HopitalNumerique\RechercheParcoursBundle\Form\Type\GuidedSearch\ShareGuidedSearchType;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\AnalyseGuidedSearchStepCommand;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\FindFirstUncompletedStepHandler;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\FindFirstUncompletedStepCommand;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\GuidedSearch\SendAnalyzesCommand;
use HopitalNumerique\RechercheParcoursBundle\Exception\GuidedSearch\Share\UserNotFoundException;
use HopitalNumerique\RechercheParcoursBundle\Exception\GuidedSearch\Share\AlreadySharedException;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\GuidedSearch\ShareGuidedSearchCommand;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\GuidedSearch\RemoveGuidedSearchCommand;

/**
 * Class GuidedSearchController
 */
class GuidedSearchController extends Controller
{
    /**
     * @param Request $request
     * @param RechercheParcoursGestion $guidedSearchConfig
     *
     * @return Response
     */
    public function indexAction(Request $request, RechercheParcoursGestion $guidedSearchConfig)
    {
        $request->getSession()->set('urlToRedirect', $request->getUri());

        $steps = $this->get('hopitalnumerique_rechercheparcours.repository.recherche_parcours')->findByGuidedSearchConfig($guidedSearchConfig);

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcours:front/index.html.twig', [
            'steps' => $steps,
        ]);
    }

    /**
     * @param RechercheParcours $guidedSearchReference
     * @param                   $guidedSearchReferenceAlias
     * @param GuidedSearch $guidedSearch
     *
     * @return RedirectResponse
     */
    public function showGuidedSearchAction(RechercheParcours $guidedSearchReference, $guidedSearchReferenceAlias, GuidedSearch $guidedSearch)
    {
        return $this->redirectToGuidedSearch($guidedSearchReference, $guidedSearchReferenceAlias, $guidedSearch);
    }

    /**
     * @param RechercheParcours $guidedSearchReference
     * @param                   $guidedSearchReferenceAlias
     *
     * @return RedirectResponse
     */
    public function showAction(RechercheParcours $guidedSearchReference, $guidedSearchReferenceAlias)
    {
        return $this->redirectToGuidedSearch($guidedSearchReference, $guidedSearchReferenceAlias);
    }

    /**
     * Redirects to the first uncompleted step that have already been viewed by the current user.
     *
     * @param RechercheParcours $guidedSearchReference
     * @param                   $guidedSearchReferenceAlias
     * @param GuidedSearch      $guidedSearch
     *
     * @return RedirectResponse
     */
    public function continueAction(RechercheParcours $guidedSearchReference, $guidedSearchReferenceAlias, GuidedSearch $guidedSearch)
    {
        $rechercheParcoursDetails = $guidedSearchReference->getRecherchesParcoursDetails();
        $subReferenceParameters = [];

        $firstUncompletedStep = $this->get(FindFirstUncompletedStepHandler::class)->handle(
            new FindFirstUncompletedStepCommand(
                $rechercheParcoursDetails,
                $guidedSearch,
                $this->getUser()
            )
        );

        if (null !== $firstUncompletedStep) {
            $reference = $this
                ->get('hopitalnumerique_rechercheparcours.repository.recherche_parcours_details')
                ->findOneBy(['id' => $firstUncompletedStep->getStepPath()])
            ;

            $stepPath = explode(':', $firstUncompletedStep->getStepPath());

            if ($reference->getShowChildren() && count($stepPath) > 1) {
                $subReference = $this
                    ->get('hopitalnumerique_reference.repository.reference')
                    ->findOneBy(['id' => $stepPath[1]])
                ;

                $subReferenceParameters = [
                    'subReference' => $subReference->getId(),
                    'subAlias' => (new Chaine($subReference->getLibelle()))->minifie(),
                ];
            }
        } else {
            $reference = $rechercheParcoursDetails->first();
        }

        return $this->redirectToRoute('hopital_numerique_guided_search_step', array_merge([
            'guidedSearch' => $guidedSearch->getId(),
            'guidedSearchReference' => $guidedSearchReference->getId(),
            'guidedSearchReferenceAlias' => $guidedSearchReferenceAlias,
            'parentReference' => $reference->getId(),
            'alias' => (new Chaine($reference->getReference()->getLibelle()))->minifie(),
        ], $subReferenceParameters));
    }

    /**
     * @param RechercheParcours $guidedSearchReference
     * @param $guidedSearchReferenceAlias
     * @param GuidedSearch|null $guidedSearch
     *
     * @return RedirectResponse
     */
    private function redirectToGuidedSearch(RechercheParcours $guidedSearchReference, $guidedSearchReferenceAlias, GuidedSearch $guidedSearch = null)
    {
        /** @var RechercheParcoursDetails $reference */
        if (!$reference = $guidedSearchReference->getRecherchesParcoursDetails()->first()) {
            return $this->redirectToRoute(
                'hopital_numerique_recherche_parcours_homepage_front',
                [
                    'id' => $guidedSearchReference->getRecherchesParcoursGestion()->getId(),
                ]
            );
        }

        $subReferenceParameters = [];
        if ($reference->getShowChildren()) {
            $subReferences = $this->get('hopitalnumerique_reference.repository.reference')
                ->getByDomainAndParent(
                    $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get(),
                    $reference->getReference()
                )
            ;

            if (count($subReferenceParameters)) {
                $subReferenceParameters = [
                    'subReference' => current($subReferences)->getId(),
                    'subAlias' => (new Chaine(current($subReferences)->getLibelle()))->minifie(),
                ];
            }
        }

        if (null === $guidedSearch) {
            $guidedSearch = $this->get('hopitalnumerique_rechercheparcours.guided_search_retriever')->retrieve($guidedSearchReference);
        }

        return $this->redirectToRoute('hopital_numerique_guided_search_step', array_merge([
            'guidedSearch' => $guidedSearch->getId(),
            'guidedSearchReference' => $guidedSearchReference->getId(),
            'guidedSearchReferenceAlias' => $guidedSearchReferenceAlias,
            'parentReference' => $reference->getId(),
            'alias' => (new Chaine($reference->getReference()->getLibelle()))->minifie(),
        ], $subReferenceParameters));
    }

    /**
     * @param RechercheParcours $guidedSearchReference
     * @param RechercheParcoursDetails $parentReference
     * @param GuidedSearch $guidedSearch
     * @param Reference|null $subReference
     *
     * @return RedirectResponse|Response
     */
    public function stepAction(
        RechercheParcours $guidedSearchReference,
        RechercheParcoursDetails $parentReference,
        GuidedSearch $guidedSearch,
        Reference $subReference = null
    ) {
        if ($guidedSearchReference !== $guidedSearch->getGuidedSearchReference() || !$this->isGranted('access', $guidedSearch)) {
            return $this->redirectToRoute(
                'hopital_numerique_guided_search_show',
                [
                    'guidedSearchReference' => $guidedSearchReference->getId(),
                    'guidedSearchReferenceAlias' => (new Chaine($guidedSearchReference->getReference()->getLibelle()))->minifie(),
                ]
            );
        }

        $this->denyAccessUnlessGranted('access', $guidedSearch);

        $domain = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();

        $subReferences = [];
        if ($parentReference->getShowChildren()) {
            $subReferences = $this->get('hopitalnumerique_reference.repository.reference')
                ->getByDomainAndParent(
                    $domain,
                    $parentReference->getReference()
                )
            ;
        }

        if (count($subReferences) && is_null($subReference)) {
            $firstSubReference = current($subReferences);

            return $this->redirectToRoute('hopital_numerique_guided_search_step', [
                'guidedSearch' => $guidedSearch->getId(),
                'guidedSearchReference' => $guidedSearchReference->getId(),
                'guidedSearchReferenceAlias' => (new Chaine($guidedSearchReference->getReference()->getLibelle()))->minifie(),
                'parentReference' => $parentReference->getId(),
                'alias' => (new Chaine($parentReference->getReference()->getLibelle()))->minifie(),
                'subReference' => $firstSubReference->getId(),
                'subAlias' => (new Chaine($firstSubReference->getLibelle()))->minifie(),
            ]);
        }

        $guidedSearchConfig = $guidedSearchReference->getRecherchesParcoursGestion();

        $stepPath = [];
        $stepPath[] = $parentReference->getId();
        if (!is_null($subReference)) {
            $stepPath[] = $subReference->getId();
        }

        $stepPath = implode(':', $stepPath);

        $guidedSearchStep =  $this->get('hopitalnumerique_rechercheparcours.guided_search_step_retriever')->retrieveGuidedSearchStep($guidedSearch, $stepPath);

        if ($guidedSearchConfig->hasPublicationType(GuidedSearchConfigPublicationType::TYPE_RISK)) {
            $riskStep['risks'] = $this->get('hopitalnumerique_rechercheparcours.factory.step_risks')->getStepRiskDTO($domain, $guidedSearch, $guidedSearchStep);
            $riskStep['addForm'] = $this->createForm(AddRiskType::class, new AddPrivateRiskCommand($guidedSearch, $this->getUser()))->createView();
            $riskStep['showRiskForm'] = $this->createForm(ShowRiskType::class, new IncludeRiskCommand($guidedSearchStep))->createView();
        }

        if ($guidedSearchConfig->hasPublicationType(GuidedSearchConfigPublicationType::TYPE_PRODUCTION)) {
            $productionStep['items'] = $this->get('hopitalnumeriquerechercheparcours.search.production')->search($guidedSearchStep);
        }

        if ($guidedSearchConfig->hasPublicationType(GuidedSearchConfigPublicationType::TYPE_HOT_POINT)) {
            $hotPointStep['items'] = $this->get('hopitalnumeriquerechercheparcours.search.hot_point')->search($guidedSearchStep);
        }

        $shareForm = null;
        if ($this->getUser()) {
            $shareForm = $this->createForm(ShareGuidedSearchType::class, new ShareGuidedSearchCommand($guidedSearch, $this->getUser()))->createView();
        }

        return $this->render('@HopitalNumeriqueRechercheParcours/RechercheParcours/front/step.html.twig', [
            'guidedSearchConfig' => $guidedSearchConfig,
            'guidedSearchStep' => $guidedSearchStep,
            'guidedSearch' => $guidedSearch,
            'guidedSearchReference' => $guidedSearchReference,
            'parentReference' => $parentReference,
            'subReferences' => $subReferences,
            'subReference' => $subReference,
            'riskStep' => isset($riskStep) ? $riskStep : [],
            'productionStep' => isset($productionStep) ? $productionStep : [],
            'hotPointStep' => isset($hotPointStep) ? $hotPointStep : [],
            'stepPath' => $stepPath,
            'shareForm' => $shareForm,
            'labelReferenceId' => Reference::PARCOURS_GUIDE,
        ]);
    }


    /**
     * @param Request $request
     * @param GuidedSearchStep $guidedSearchStep
     *
     * @return RedirectResponse
     */
    public function analyseAction(Request $request, GuidedSearchStep $guidedSearchStep)
    {
        $this->denyAccessUnlessGranted('access', $guidedSearchStep->getGuidedSearch());

        if (is_null($this->getUser())) {
            $request->getSession()->set('urlToRedirect', $request->getUri());

            return $this->redirectToRoute('account_login');
        }

        $this->get('hopitalnumerique_rechercheparcours.handler.analyse_guided_search_step_command')->handle(new AnalyseGuidedSearchStepCommand($guidedSearchStep));

        return $this->redirect($this->get('hopitalnumerique_rechercheparcours.helper.step_url_generator')->generate($guidedSearchStep));
    }

    /**
     * @param Request          $request
     * @param GuidedSearchStep $guidedSearchStep
     * @param bool             $redirectPrevious
     *
     * @return RedirectResponse
     */
    public function shareAction(Request $request, GuidedSearchStep $guidedSearchStep, $redirectPrevious = false)
    {
        $this->denyAccessUnlessGranted('access', $guidedSearchStep->getGuidedSearch());

        if (is_null($this->getUser())) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $command = new ShareGuidedSearchCommand($guidedSearchStep->getGuidedSearch(), $this->getUser());
        $shareForm = $this->createForm(ShareGuidedSearchType::class, $command);

        $shareForm->handleRequest($request);

        if ($shareForm->isSubmitted() && $shareForm->isValid()) {
            try {
                $this->get('hopitalnumerique_rechercheparcours.handler.share_guided_search_command')->handle($command);

                $this->addFlash('success', $this->get('translator')->trans('step.share.notifications.success', [], 'guided_search'));
            } catch (AlreadySharedException $e) {
                $this->addFlash('danger', $this->get('translator')->trans('step.share.notifications.already_shared_with_user', [], 'guided_search'));
            } catch (UserNotFoundException $e) {
                $this->addFlash('danger', $this->get('translator')->trans('step.share.notifications.user_not_found', [], 'guided_search'));
            }
        } else {
            $this->addFlash('danger', $this->get('translator')->trans('step.share.notifications.error', [], 'guided_search'));
        }

        if ($redirectPrevious) {
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->redirect($this->get('hopitalnumerique_rechercheparcours.helper.step_url_generator')->generate($guidedSearchStep));
    }

    /**
     * @param Request      $request
     * @param GuidedSearch $guidedSearch
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, GuidedSearch $guidedSearch)
    {
        $this->denyAccessUnlessGranted('access', $guidedSearch);

        $command = new RemoveGuidedSearchCommand($guidedSearch, $this->getUser());

        try {
            $this->get('hopitalnumerique_rechercheparcours.handler.remove_guided_search_command')->handle($command);

            $this->addFlash('success', $this->get('translator')->trans('guided_search.delete.success', [], 'widget'));
        } catch (\Exception $exception) {
            $this->addFlash('danger', $this->get('translator')->trans('guided_search.delete.error', [], 'widget'));

            return $this->redirect($request->headers->get('referer'));
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @param Request      $request
     * @param GuidedSearch $guidedSearch
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function sendAction(Request $request, GuidedSearch $guidedSearch)
    {
        $this->denyAccessUnlessGranted('access', $guidedSearch);

        $sendResultMail =
            $this->get('nodevo_mail.manager.mail')->findOneById(Mail::MAIL_SHARE_GUIDED_SEARCH_ID);
        if (null === $sendResultMail) {
            throw new \Exception($this->get('translator')->trans('guided_search.send.not_found', [], 'widget'));
        }

        $shareForm = $this->createForm(RecommandationType::class, null, [
            'mail' => $sendResultMail,
            'expediteur' => $this->getUser(),
            'url' => $request->headers->get('referer'),
            'action' => $this
                ->redirectToRoute('hopital_numerique_guided_search_send', ['guidedSearch' => $guidedSearch->getId()])
                ->getTargetUrl()
            ,
        ]);
        $shareForm->handleRequest($request);

        if ($shareForm->isSubmitted()) {
            $command = new SendAnalyzesCommand(
                $guidedSearch,
                $this->getUser(),
                $shareForm->get('expediteur')->getData(),
                $shareForm->get('destinataire')->getData(),
                $shareForm->get('objet')->getData(),
                $shareForm->get('message')->getData()
            );

            $this->get('hopitalnumerique_rechercheparcours.handler.send_guided_search_analyzes_command')->handle(
                $command
            );

            $this->addFlash('success', $this->get('translator')->trans('guided_search.send.success', [], 'widget'));

            return $this->redirect($shareForm->get('url')->getData());
        }

        return $this->render(
            '@HopitalNumeriqueAutodiag/Restitution/popin.html.twig',
            [
                'recommandationForm' => $shareForm->createView(),
            ]
        );
    }
}
