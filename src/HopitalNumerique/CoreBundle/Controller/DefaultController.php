<?php

namespace HopitalNumerique\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
    public function indexAction()
    {
        $article = $this->get('hopitalnumerique_objet.manager.objet')->getArticleHome();

        //get actus
        $allCategories = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'parent' => 188) );
        $user          = $this->get('security.context')->getToken()->getUser();
        $role          = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $actualites    = $this->get('hopitalnumerique_objet.manager.objet')->getActualitesByCategorie( $allCategories, $role, 2 );

        // Get publications (production)
        $publications  = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByNbVue(array(425,176,177,178,179,180,181,182,435), 3);

        // Get nombres comptes créés
        $nb_comptes    = count($this->get('hopitalnumerique_user.manager.user')->getAllUsers());

        // Get nombres de fils sur le forum
        $boards = $this->get('ccdn_forum_forum.model.board')->findAllBoards();
        $i = 0;
        foreach($boards as $board ) {
          $topics = $board->getTopics();
          foreach ($topics as $topic) {
            $i++;
          }
        }
        $nb_fils = $i;


        // Get last topic by forum
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

        $topics = $this->get('hopitalnumerique_forum.manager.topic')->getLastTopicsForum($idForum,6);

        return $this->render('HopitalNumeriqueCoreBundle:Default:index.html.twig', array(
            'article'      => $article,
            'actualites'   => $actualites,
            'publications' => $publications,
            'nb_comptes'   => $nb_comptes,
            'nb_fils'      => $nb_fils,
            'topics'       => $topics,
            'forumName'    => $forumName
        ));


    }
}