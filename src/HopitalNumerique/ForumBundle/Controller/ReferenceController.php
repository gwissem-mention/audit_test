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
    /**
     * [topicAction description]
     *
     * @param  Topic  $topic [description]
     *
     * @return [type]
     */
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
    
    /**
     * [topicOwnAction description]
     *
     * @param  Topic  $topic [description]
     *
     * @return [type]
     */
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

    /**
     * [saveTopicAction description]
     *
     * @param  Topic  $topic [description]
     *
     * @return [type]
     */
    public function saveTopicAction( Topic $topic )
    {
        //efface toutes les anciennes références
        $oldRefs = $this->get('hopitalnumerique_forum.manager.reftopic')->findBy( array('topic' => $topic) );
        $this->get('hopitalnumerique_forum.manager.reftopic')->delete( $oldRefs );

        //ajoute les nouvelles références
        $references = json_decode( $this->get('request')->request->get('references') );
        $refToAdd   = array();
        foreach( $references as $reference ) {
            $ref = $this->get('hopitalnumerique_forum.manager.reftopic')->createEmpty();
            $ref->setTopic( $topic );
            $ref->setPrimary( $reference->type );

            //get ref
            $ref->setReference( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $reference->id) ) );

            //save it
            $refToAdd[] = $ref;
        }

        $this->get('hopitalnumerique_forum.manager.reftopic')->save( $refToAdd );

        //get Topic Note
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $note = $this->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $topic->getReferences(), $refsPonderees );

        return new Response('{"success":true, "note":'.$note.'}', 200);
    }
}