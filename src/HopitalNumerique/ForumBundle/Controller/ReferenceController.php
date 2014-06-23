<?php

namespace HopitalNumerique\ForumBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use HopitalNumerique\ForumBundle\Entity\Topic;

/**
 * Reference controller.
 */
class ReferenceController extends Controller
{
    public function topicAction( Topic $topic )
    {
        //get references and selected references as One Array
        $results    = $this->get('hopitalnumerique_reference.manager.reference')->getRefsForGestionObjets();
        $references = $this->get('hopitalnumerique_forum.manager.topic')->getReferences($topic, $results);

        return $this->render('HopitalNumeriqueForumBundle:Reference:manage.html.twig', array(
            'references' => $references,
            'topic'      => true,
            'contenu'    => 'null',
            'titre'      => $topic->getTitle()
        ));
    }
    
    public function topicOwnAction( Topic $topic )
    {
        //get references and selected references as One Array
        $references = $this->get('hopitalnumerique_forum.manager.topic')->getReferencesOwn($topic);

        return $this->render('HopitalNumeriqueForumBundle:Reference:manage-own.html.twig', array(
            'references' => $references,
            'topic'      => true,
            'contenu'    => 'null'
        ));
    }

    public function contenuAction( $id )
    {
        //Récupération du contenu passée en paramètre
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array('id' => $id) );

        //get references and selected references as One Array
        $results    = $this->get('hopitalnumerique_reference.manager.reference')->getRefsForGestionObjets();
        $references = $this->get('hopitalnumerique_objet.manager.contenu')->getReferences($contenu, $results);

        return $this->render('HopitalNumeriqueObjetBundle:Reference:manage.html.twig', array(
            'references' => $references,
            'objet'      => 'false',
            'contenu'    => $id,
            'titre'      => $contenu->getTitre()
        ));
    }

    public function contenuOwnAction( $id )
    {
        //Récupération du contenu passée en paramètre
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array('id' => $id) );

        //get references and selected references as One Array
        $references = $this->get('hopitalnumerique_objet.manager.contenu')->getReferencesOwn($contenu);
        
        return $this->render('HopitalNumeriqueObjetBundle:Reference:manage-own.html.twig', array(
            'references' => $references,
            'objet'      => 'false',
            'contenu'    => $id
        ));
    }

    public function saveTopicAction( Topic $topic )
    {
        //efface toutes les anciennes références
        $oldRefs = $this->get('hopitalnumerique_forum.manager.reftopic')->findBy( array('topic' => $topic) );
        $this->get('hopitalnumerique_forum.manager.reftopic')->delete( $oldRefs );

        //ajoute les nouvelles références
        $nbRef      = 0;
        $references = json_decode( $this->get('request')->request->get('references') );
        foreach( $references as $reference ) {
            $ref = $this->get('hopitalnumerique_forum.manager.reftopic')->createEmpty();
            $ref->setTopic( $topic );
            $ref->setPrimary( $reference->type );

            //get ref
            $ref->setReference( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $reference->id) ) );

            //save it
            $this->get('hopitalnumerique_forum.manager.reftopic')->save( $ref );

            $nbRef++;
        }

        return new Response('{"success":true, "nbRef":'.$nbRef.'}', 200);
    }

    public function saveContenuAction()
    {
        //on récupère le contenu
        $id      = $this->get('request')->request->get('contenu');
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array('id' => $id) );
        
        //efface toutes les anciennes références
        $oldRefs = $this->get('hopitalnumerique_objet.manager.refcontenu')->findBy( array('contenu' => $contenu) );
        $this->get('hopitalnumerique_objet.manager.refcontenu')->delete( $oldRefs );

        //ajoute les nouvelles références
        $nbRef      = 0;
        $references = json_decode( $this->get('request')->request->get('references') );
        foreach( $references as $reference ) {
            $ref = $this->get('hopitalnumerique_objet.manager.refcontenu')->createEmpty();
            $ref->setContenu( $contenu );
            $ref->setPrimary( $reference->type );

            //get ref
            $ref->setReference( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $reference->id) ) );

            //save it
            $this->get('hopitalnumerique_objet.manager.refcontenu')->save( $ref );

            $nbRef++;
        }

        return new Response('{"success":true, "nbRef":'.$nbRef.'}', 200);
    }
}