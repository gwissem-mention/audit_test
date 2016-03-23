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