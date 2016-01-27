<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Contrôleur concernant les publications de la communauté de pratique.
 */
class PublicationController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affiche toutes les publications disponibles.
     */
    public function listAction($page)
    {
    	if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
    			->canAccessCommunautePratique()) {
    				return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
    	}
    	
        $groupes = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
            ->findWithPublications();
        $groupesAdapter = new ArrayAdapter($groupes);
        $groupesPager = new Pagerfanta($groupesAdapter);
        $groupesPager->setMaxPerPage(2);
        $groupesPager->setCurrentPage($page);
        
        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Publication:list.html.twig',
            array(
                'groupesPager' => $groupesPager,
                'groupes' => $groupes
            )
        );
    }

    /**
     * Affiche toutes les publications d'un groupe.
     */
    public function listByGroupeAction(Groupe $groupe)
    {
        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Publication:listByGroupe.html.twig',
            array(
                'groupe' => $groupe,
                'groupes' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findWithPublications()
            )
        );
    }
}
