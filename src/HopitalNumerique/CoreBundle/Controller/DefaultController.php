<?php

namespace HopitalNumerique\CoreBundle\Controller;

use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController.
 */
class DefaultController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $article = $this->get('hopitalnumerique_objet.manager.objet')->getArticleHome();

        //get actus
        $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findByParent(
            $this->get('hopitalnumerique_reference.manager.reference')->findOneById(188)
        );
        $user = $this->getUser();
        $role = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $actualites = $this->get('hopitalnumerique_objet.manager.objet')->getActualitesByCategorie(
            $allCategories,
            $role,
            3,
            ['champ' => 'obj.dateCreation', 'tri' => 'DESC']
        );

        // Get current domain
        $domaine = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();

        // Get publications (production)
        $publications = $this->get('hopitalnumerique_objet.repository.objet')->getObjetsByNbVue($domaine);

        $typeArticleCarrousel = $this->get('hopitalnumerique_reference.manager.reference')->findBy(['id' => 520]);
        $articlesALaUne = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByTypes($typeArticleCarrousel);

        // Get nombres comptes créés
        $nb_eta = $this->get('hopitalnumerique_user.manager.user')->getNbEtablissements(
            $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()
        );

        // Définition du forum en fonction de l'utilisateur connecté
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // authenticated (NON anonymous)
            $usr = $this->getUser();
            switch ($usr->getRole()) {
                case 'ROLE_AMBASSADEUR_7':
                    $idForum = 3;
                    $forumName = 'Ambassadeurs';
                    break;
                case 'ROLE_EXPERT_6':
                    $idForum = 2;
                    $forumName = 'Experts';
                    break;
                case 'ROLE_ARS_CMSI_4':
                case 'ROLE_ARS_-_CMSI_101':
                    $idForum = 4;
                    $forumName = 'CMSI';
                    break;
                default:
                    $idForum = 1;
                    $forumName = 'Public';
            }
        } else {
            $idForum = 1;
            $forumName = 'Public';
        }

        $cdpDiscussions = $this->get(DiscussionRepository::class)->getRecentPublicDiscussionActivity($domaine, 3);
        $nbDiscussions = $this->get(DiscussionRepository::class)->getPublicDiscussionCount();

        // Get Article à la une
        $alaune = $this->get('hopitalnumerique_objet.manager.objet')->getArticleAlaUne();

        // Get nombres de publications consultées
        $nb_pub_consultees = $this->get('hopitalnumerique_objet.manager.objet')->getNbVuesPublication();

        $view = 'HopitalNumeriqueCoreBundle:Templates/'
                . $request->getSession()->get('templateId')
                . ':index.html.twig';

        return $this->render(
            $view,
            [
                'article' => $article,
                'actualites' => $actualites,
                'publications' => $publications,
                'articlesALaUne' => $articlesALaUne,
                'nb_eta' => $nb_eta,
                'nb_discussions' => $nbDiscussions,
                'cdpDiscussions' => $cdpDiscussions,
                'forumName' => $forumName,
                'alaune' => $alaune,
                'nb_consultations' => $nb_pub_consultees,
            ]
        );
    }
}
