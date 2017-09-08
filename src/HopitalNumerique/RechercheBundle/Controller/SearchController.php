<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nodevo\MailBundle\Form\Type\RecommandationType;
use HopitalNumerique\RechercheBundle\Entity\Requete;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\RechercheBundle\Service\SearchEmailGenerator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class SearchController
 */
class SearchController extends Controller
{

    /**
     * Génération manuelle d'une requete de recherche en fonction
     * d'un tableau d'id de reference passé en param, d'une recherche textuelle et de type(s).
     *
     * @param string $refs Liste des références à explode
     * @param null   $q    Recherche textuelle
     * @param null   $type Liste des références à explode
     *
     * @return RedirectResponse
     */
    public function generateManuallyRequeteAction($refs = null, $q = null, $type = null)
    {
        $referenceIds = ('null' != $refs ? explode(',', $refs) : []);
        $searchedText = ($q == 'null' ? '' : $q);
        $publicationCategoryIds = ($type == 'null' ? [] : explode(',', $type));

        $referencementRequeteSession = $this->get(
            'hopitalnumerique_recherche.dependency_injection.referencement.requete_session'
        );

        $referencementRequeteSession->setReferenceIds($referenceIds);
        $referencementRequeteSession->setPublicationCategoryIds($publicationCategoryIds);
        $referencementRequeteSession->setSearchedText($searchedText);

        return $this->redirectToRoute('hopital_numerique_recherche_homepage');
    }

    /**
     * @param Request $request
     * @param Requete $search
     *
     * @return RedirectResponse|Response
     */
    public function sendAction(Request $request, Requete $search)
    {
        if ($this->getUser()->getId() !== $search->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        $sendSearchEmail = $this->get(SearchEmailGenerator::class)->generateSearchEmail($search);

        $sendSearchForm = $this->createForm(RecommandationType::class, null, [
            'mail' => $sendSearchEmail,
            'expediteur' => $this->getUser(),
            'url' => $request->headers->get('referer'),
            'action' => $this->redirectToRoute(
                'hopital_numerique_recherche_send',
                [
                    'search' => $search->getId(),
                ]
            )->getTargetUrl(),
        ]);

        $sendSearchForm->handleRequest($request);

        if ($sendSearchForm->isSubmitted()) {
            $this->container->get('nodevo_mail.manager.mail')->sendSearch(
                $sendSearchForm->get('expediteur')->getData(),
                $sendSearchForm->get('destinataire')->getData(),
                $sendSearchForm->get('objet')->getData(),
                $sendSearchForm->get('message')->getData()
            );

            $this->addFlash('success', $this->get('translator')->trans('saved_searches.send.success', [], 'widget'));

            return $this->redirect($sendSearchForm->get('url')->getData());
        }

        return $this->render(
            '@HopitalNumeriqueAutodiag/Restitution/popin.html.twig',
            [
                'recommandationForm' => $sendSearchForm->createView(),
            ]
        );
    }
}
