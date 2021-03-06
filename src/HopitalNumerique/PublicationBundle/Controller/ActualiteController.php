<?php

namespace HopitalNumerique\PublicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

class ActualiteController extends Controller
{
    /**
     * @var int Nombre d'actualités max à afficher par page
     */
    const NOMBRE_ACTUALITES_PAR_PAGE = 5;

    /**
     * Article Action.
     */
    public function indexAction($page = 1)
    {
        //on récupère les actus
        $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findByParent($this->get('hopitalnumerique_reference.manager.reference')->findOneById(188));
        $user = $this->get('security.context')->getToken()->getUser();
        $role = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $actualites = $this->get('hopitalnumerique_objet.manager.objet')
            ->getActualitesByCategorie(
                $allCategories,
                $role,
                0,
                ['champ' => 'obj.dateModification', 'tri' => 'DESC'],
                $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()
            )
        ;

        $actualitesAdapter = new ArrayAdapter($actualites);
        $actualitesPager = new Pagerfanta($actualitesAdapter);
        $actualitesPager->setMaxPerPage(self::NOMBRE_ACTUALITES_PAR_PAGE);
        $actualitesPager->setCurrentPage($page);

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Actualite:index.html.twig', [
            'actualitesPager' => $actualitesPager,
            'type' => 'normal',
        ]);
    }

    /**
     * Article ambassadeur.
     */
    public function ambassadeurAction($page = 1)
    {
        //on récupère les actus
        $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findByParent($this->get('hopitalnumerique_reference.manager.reference')->findOneById(570));
        $user = $this->get('security.context')->getToken()->getUser();
        $role = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $actualites = $this->get('hopitalnumerique_objet.manager.objet')
            ->getActualitesByCategorie(
                $allCategories,
                $role,
                0,
                ['champ' => 'obj.dateModification', 'tri' => 'DESC'],
                $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()
            )
        ;

        $actualitesAdapter = new ArrayAdapter($actualites);
        $actualitesPager = new Pagerfanta($actualitesAdapter);
        $actualitesPager->setMaxPerPage(self::NOMBRE_ACTUALITES_PAR_PAGE);
        $actualitesPager->setCurrentPage($page);

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Actualite:index.html.twig', [
            'actualitesPager' => $actualitesPager,
            'type' => 'ambassadeur',
        ]);
    }

    /**
     * Article Action.
     */
    public function categorieAction($id, $libelle, $type, $page = 1)
    {
        //on récupère les actus
        $categories = $this->get('hopitalnumerique_reference.manager.reference')->findBy(['id' => $id]);
        $user = $this->get('security.context')->getToken()->getUser();
        $role = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $actualites = $this->get('hopitalnumerique_objet.manager.objet')
            ->getActualitesByCategorie(
                $categories,
                $role,
                0,
                ['champ' => 'obj.dateModification', 'tri' => 'DESC'],
                $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()
            )
        ;

        $actualitesAdapter = new ArrayAdapter($actualites);
        $actualitesPager = new Pagerfanta($actualitesAdapter);
        $actualitesPager->setMaxPerPage(self::NOMBRE_ACTUALITES_PAR_PAGE);
        $actualitesPager->setCurrentPage($page);

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Actualite:index.html.twig', [
            'actualitesPager' => $actualitesPager,
            'type' => $type,
        ]);
    }

    /**
     * Partial render : bloc liste des actualités colonne left.
     *
     * @param null $type
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function actualitesAction($type = null)
    {
        if ('ambassadeur' === $type) {
            $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findByParent(
                $this->get('hopitalnumerique_reference.manager.reference')->findOneById(570)
            );
        } else {
            $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findByParent(
                $this->get('hopitalnumerique_reference.manager.reference')->findOneById(188)
            );
        }

        //Show categ with articles only
        $categories = $this
            ->get('hopitalnumerique_objet.manager.objet')
            ->getCategoriesWithArticles(
                $allCategories,
                $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()
            )
        ;

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Actualite:actualites.html.twig', [
            'categories' => $categories,
            'type' => $type,
        ]);
    }
}
