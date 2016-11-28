<?php
namespace HopitalNumerique\PublicationBundle\Controller\Front;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\PublicationBundle\Entity\Suggestion;
use HopitalNumerique\PublicationBundle\Form\Type\SuggestionType;

use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SuggestionController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        if (!$this->getUser() instanceof User) {
            $this->addFlash('danger', 'Vous devez être connecté pour proposer une suggestion');

            return $this->redirectToRoute('hopital_numerique_homepage');
        }

        /** @var Suggestion $suggestion */
        $suggestion = new Suggestion();

        $form = $this->createForm(SuggestionType::class, $suggestion, ['validation_groups' => ['front_add']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $suggestion->addDomain($this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get());
            $suggestion->setCreationDate(new \DateTime('now'));
            $suggestion->setState(
                $this->get('doctrine.orm.entity_manager')
                    ->getReference(Reference::class, Reference::ETAT_SUGGESTION_DEMANDE_ID)
            );
            $suggestion->setUser($this->getUser());

            $this->getDoctrine()->getManager()->persist($suggestion);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('hopitalnumerique_suggestion_front_edit', [
                'suggestion' => $suggestion->getId(),
            ]);
        }

        return $this->render('HopitalNumeriquePublicationBundle:Suggestion:add.html.twig', [
            'form' => $form->createView(),
            'suggestion' => $suggestion,
            'commonDomainsWithUser' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')
                ->getEntityDomainesCommunsWithUser($suggestion, $this->getUser()),
        ]);
    }

    /**
     * @param Suggestion $suggestion
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Suggestion $suggestion)
    {
        if ($suggestion->getUser() != $this->getUser()) {
            $this->addFlash('danger', 'Vous n\'avez pas la permission d\'accéder à cette suggestion.');

            return $this->redirectToRoute('hopitalnumerique_suggestion_front_add');
        }

        $form = $this->createForm(SuggestionType::class, $suggestion, ['validation_groups' => ['front_add']]);

        return $this->render('HopitalNumeriquePublicationBundle:Suggestion:add.html.twig', [
            'form' => $form->createView(),
            'suggestion' => $suggestion,
        ]);
    }

    public function validateAction()
    {
        $this->addFlash('success', 'Merci pour votre participation !');

        return $this->redirectToRoute('hopitalnumerique_suggestion_front_add');
    }
}
