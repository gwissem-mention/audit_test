<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Contrôleur des actualités de la communauté de pratiques.
 */
class ActualiteController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Liste des actualités.
     */
    public function listAction($page, Request $request)
    {
        $categorie = $this->container->get('hopitalnumerique_reference.manager.reference')
            ->findOneById(Reference::ARTICLE_CATEGORIE_COMMUNAUTE_DE_PRATIQUES_ID);

        return $this->renderList($categorie, $page, $request);
    }

    /**
     * Liste les actualités d'une catégorie.
     */
    public function listByCategorieAction(Reference $categorie, $page, Request $request)
    {
        return $this->renderList($categorie, $page, $request);
    }

    /**
     * Affiche la liste des articles d'une catégorie.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $categorie Catégorie
     * @param integer $page Numéro de page
     */
    private function renderList(Reference $categorie, $page, Request $request)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canAccessCommunautePratique()) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $domaine = $this->container->get('hopitalnumerique_domaine.manager.domaine')
            ->findOneById($request->getSession()->get('domaineId'));

        $actualites = $this->container->get('hopitalnumerique_objet.manager.objet')
            ->getArticlesForCategorie($categorie, $domaine);
        $actualitesAdapter = new ArrayAdapter($actualites);
        $actualitesPager = new Pagerfanta($actualitesAdapter);
        $actualitesPager->setMaxPerPage(5);
        $actualitesPager->setCurrentPage($page);

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Actualite:list.html.twig', array(
            'categorieActualites' => $this->container->get('hopitalnumerique_reference.manager.reference')
                ->findOneById(Reference::ARTICLE_CATEGORIE_COMMUNAUTE_DE_PRATIQUES_ID),
            'categorie' => $categorie,
            'actualitesPager' => $actualitesPager
        ));
    }
}
