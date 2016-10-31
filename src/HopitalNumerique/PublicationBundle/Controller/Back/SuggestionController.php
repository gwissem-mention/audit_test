<?php
namespace HopitalNumerique\PublicationBundle\Controller\Back;

use HopitalNumerique\PublicationBundle\Entity\Suggestion;
use HopitalNumerique\PublicationBundle\Form\Type\SuggestionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     * @param Suggestion $suggestion
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Suggestion $suggestion)
    {
        $form = $this->createForm(SuggestionType::class, $suggestion);

        return $this->render('HopitalNumeriquePublicationBundle:Suggestion:edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
