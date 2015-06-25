<?php

namespace HopitalNumerique\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 */
class DefaultController extends Controller
{
    /**
     * [indexAction description]
     *
     * @return [type]
     */
    public function indexAction(Request $request)
    {
        $article = $this->get('hopitalnumerique_objet.manager.objet')->getArticleHome();

        //get actus
        $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'parent' => 188) );
        $user          = $this->get('security.context')->getToken()->getUser();
        $role          = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $actualites    = $this->get('hopitalnumerique_objet.manager.objet')->getActualitesByCategorie( $allCategories, $role, 3, array( 'champ' => 'obj.dateCreation', 'tri' => 'DESC') );

        // Get publications (production)
        $publications  = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByNbVue();

        $typeArticleCarrousel = $this->get('hopitalnumerique_reference.manager.reference')->findBy(array('id' => 520 ));
        $articlesALaUne       = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByTypes($typeArticleCarrousel);

        // Get nombres comptes créés
        $nb_eta = $this->get('hopitalnumerique_user.manager.user')->getNbEtablissements();

        // Définition du forum en fonction de l'utilisateur connecté
        if( $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ){
          // authenticated (NON anonymous)
          $usr = $this->getUser();
          switch($usr->getRole()) {
            case 'ROLE_AMBASSADEUR_7':
              $idForum = 3;
              $forumName = "Ambassadeurs";
              break;
            case 'ROLE_EXPERT_6':
              $idForum = 3;
              $forumName = "Experts";
              break;
            case 'ROLE_ARS_CMSI_4':
            case 'ROLE_ARS_-_CMSI_101':
              $idForum = 4;
              $forumName = "CMSI";
              break;
            default:
              $idForum = 1;
              $forumName = "Public";
          }
        } else {
          $idForum = 1;
          $forumName = "Public";
        }

        // Get nombres de fils sur le forum
        $boards = $this->get('ccdn_forum_forum.model.board')->findAllBoards();
        $i = 0;
        foreach($boards as $board ) {
          $topics = $board->getTopics();
          $cat = $board->getCategory();
          if($cat->getForum()->getId() == $idForum) {
            foreach ($topics as $topic) {
              $i++;
            }
          }
        }
        $nb_fils = $i;
        $topics = $this->get('hopitalnumerique_forum.manager.topic')->getLastTopicsForum($idForum,5);

        // Get Article à la une
        $alaune = $this->get('hopitalnumerique_objet.manager.objet')->getArticleAlaUne();

        // Get nombres de publications consultées
        // $nb_pub_consultees = $this->get('hopitalnumerique_objet.manager.consultation')->getNbConsultations();
        $nb_pub_consultees = $this->get('hopitalnumerique_objet.manager.objet')->getNbVuesPublication();

        $view = 'HopitalNumeriqueCoreBundle:Templates/' .  $request->getSession()->get('templateId') . ':index.html.twig'; 

        return $this->render($view, array(
            'article'          => $article,
            'actualites'       => $actualites,
            'publications'     => $publications,
            'articlesALaUne'   => $articlesALaUne,
            'nb_eta'           => $nb_eta,
            'nb_fils'          => $nb_fils,
            'topics'           => $topics,
            'forumName'        => $forumName,
            'alaune'           => $alaune,
            'nb_consultations' => $nb_pub_consultees
        ));
    }
}