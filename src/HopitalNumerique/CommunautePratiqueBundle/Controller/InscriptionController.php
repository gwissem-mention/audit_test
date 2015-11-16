<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Contrôleur gérant l'inscription à la communauté de pratique.
 */
class InscriptionController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Inscrit l'utilisateur connecté.
     */
    public function ajaxInscritAction()
    {
        $user = $this->getUser();
        
        if (null !== $user && !$this->container->get('hopitalnumerique_communautepratique.dependency_injection.inscription')->hasInformationManquante($user))
        {
            $user->setInscritCommunautePratique(true);
            $this->container->get('hopitalnumerique_user.manager.user')->save($user);
            $this->get('session')->getFlashBag()->add( 'success', 'L\'inscription à la communauté de pratiques a été confirmée.' );
            
            $articleCommunautePratique = $this->container->get('hopitalnumerique_objet.manager.objet')->findOneById(Objet::ARTICLE_COMMUNAUTE_PRATIQUE_ID);
            return new JsonResponse( array( 'url' => $this->generateUrl('hopital_numerique_publication_publication_article', array('categorie' => 'article', 'id' => $articleCommunautePratique->getId(), 'alias' => $articleCommunautePratique->getAlias())) ) );
        }
        else
        {
            $this->get('session')->getFlashBag()->add( 'danger', 'L\'inscription à la communauté de pratiques a échouée.' );
            return new JsonResponse( array( 'url' => $this->generateUrl('hopital_numerique_homepage') ) );
        }
    }
    
    /**
     * Désinscrit l'utilisateur connecté.
     */
    public function ajaxDesinscritAction()
    {
        $user = $this->getUser();
        
        if (null !== $user)
        {
            $user->setInscritCommunautePratique(false);
            $this->container->get('hopitalnumerique_user.manager.user')->save($user);
            $this->get('session')->getFlashBag()->add( 'success', 'Vous avez bien quitté la communauté. Vous pouvez vous y ré-inscrire à tout moment, merci de votre participation !' );
            
            $articleCommunautePratique = $this->container->get('hopitalnumerique_objet.manager.objet')->findOneById(Objet::ARTICLE_COMMUNAUTE_PRATIQUE_ID);
            return new JsonResponse( array( 'url' => $this->generateUrl('hopital_numerique_publication_publication_article', array('categorie' => 'article', 'id' => $articleCommunautePratique->getId(), 'alias' => $articleCommunautePratique->getAlias())) ) );
        }
        else
        {
            $this->get('session')->getFlashBag()->add( 'danger', 'La désinscription de la communauté de pratiques a échouée.' );
            return new JsonResponse( array( 'url' => $this->generateUrl('hopital_numerique_homepage') ) );
        }
    }
}
