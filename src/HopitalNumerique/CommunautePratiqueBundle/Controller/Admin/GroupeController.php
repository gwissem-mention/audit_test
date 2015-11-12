<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller\Admin;

/**
 * Contrôleur des groupes dans l'admin.
 */
class GroupeController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affiche les groupes à gérer.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $groupeGrid = $this->get('hopitalnumerique_communautepratique.grid.groupe');

        return $groupeGrid->render('HopitalNumeriqueCommunautePratiqueBundle:Admin/Groupe:list.html.twig');
    }
}
