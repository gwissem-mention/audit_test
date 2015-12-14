<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
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
        $groupesQueryBuilder = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
            ->findWithPublicationsQueryBuilder();
        $groupesAdapter = new DoctrineORMAdapter($groupesQueryBuilder);
        $groupesPager = new Pagerfanta($groupesAdapter);
        $groupesPager->setMaxPerPage(2);
        $groupesPager->setCurrentPage($page);
        
        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Publication:list.html.twig',
            array(
                'groupesPager' => $groupesPager
            )
        );
    }
}
