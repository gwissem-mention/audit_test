<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;

/**
 * Contrôleur concernant les fiches.
 */
class FicheController extends Controller
{
    /**
     * Ajout d'une fiche
     */
    public function addAction(Groupe $groupe, Request $request)
    {
        $nouvelleFiche = $this->container->get('hopitalnumerique_communautepratique.manager.fiche')->createEmpty();
        $nouvelleFiche->setGroupe($groupe);

        return $this->editAction($nouvelleFiche, $request);
    }

    /**
     * Édition d'une fiche.
     */
    public function editAction(Fiche $fiche, Request $request)
    {
        $ficheFormulaire = $this->createForm('hopitalnumerique_communautepratiquebundle_fiche', $fiche);
        $ficheFormulaire->handleRequest($request);

        if ($ficheFormulaire->isSubmitted())
        {
            if ($ficheFormulaire->isValid())
            {
                $this->container->get('hopitalnumerique_communautepratique.manager.fiche')->save($fiche);
                $this->container->get('session')->getFlashBag()->add( 'success', 'Fiche enregistrée avec succès.' );
                return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_groupe_view', array( 'groupe' => $fiche->getGroupe()->getId() )));
            }
            else
            {
                $this->container->get('session')->getFlashBag()->add( 'danger', 'Fiche non enregistrée.' );
            }
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Fiche:edit.html.twig', array(
            'ficheFormulaire' => $ficheFormulaire->createView(),
            'fiche' => $fiche
        ));
    }
}
