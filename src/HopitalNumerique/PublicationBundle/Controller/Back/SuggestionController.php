<?php
namespace HopitalNumerique\PublicationBundle\Controller\Back;

use HopitalNumerique\PublicationBundle\Entity\Suggestion;
use HopitalNumerique\PublicationBundle\Form\Type\SuggestionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SuggestionController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_publication.grid.suggestion');

        return $grid->render('HopitalNumeriquePublicationBundle:Suggestion:index.html.twig');
    }

    /**
     * @param Request $request
     * @param Suggestion $suggestion
     * @return Response
     */
    public function editAction(Request $request, Suggestion $suggestion)
    {
        $form = $this->createForm(SuggestionType::class, $suggestion);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            //@TODO: Id de la référence pas en dur
            if ($suggestion->getState()->getId() == 2006) {
                $this->get('hopitalnumerique_publication.service.suggestion_converter')
                    ->suggestionConverter($suggestion)
                ;
            }

            $entityManager->persist($suggestion);
            $entityManager->flush();

            $this->addFlash('info', 'Suggestion mise à jour.');

            if ($request->get('do') === 'save-close') {
                return $this->redirectToRoute('hopitalnumerique_suggestion_back_index');
            }
        }

        return $this->render('HopitalNumeriquePublicationBundle:Suggestion:edit.html.twig', [
            'form' => $form->createView(),
            'suggestion' => $suggestion,
            'commonDomainsWithUser' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')
                ->getEntityDomainesCommunsWithUser($suggestion, $this->getUser()),
        ]);
    }

    /**
     * @param Suggestion $suggestion
     *
     * @return Response
     */
    public function deleteAction(Suggestion $suggestion)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($suggestion);
        $entityManager->flush();

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return new Response('{"success":true, "url" : "' . $this->generateUrl('hopitalnumerique_suggestion_back_index') . '"}', 200);
    }

    /**
     * @return Response
     */
    public function isFileExistAction()
    {
        $fileName = $this->get('request')->request->get('fileName');
        $fileName = explode('\\', $fileName);

        $objet = $this->getDoctrine()->getRepository('HopitalNumeriquePublicationBundle:Suggestion')
            ->findOneBy(['path' => end($fileName)])
        ;

        $result = is_null($objet) ? 'false' : 'true';

        return new Response('{"success":' . $result . '}', 200);
    }
}
