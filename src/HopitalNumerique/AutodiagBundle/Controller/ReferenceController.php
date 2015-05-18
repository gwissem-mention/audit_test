<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Chapitre;
use HopitalNumerique\AutodiagBundle\Entity\Question;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reference controller.
 */
class ReferenceController extends Controller
{
    /**
     * Affiche les références du chapitre
     *
     * @param Chapitre $chapitre Le chapitre
     */
    public function chapitreAction( Chapitre $chapitre )
    {
        //get references and selected references as One Array
        $results    = $this->get('hopitalnumerique_reference.manager.reference')->getRefsForGestionObjets();
        $references = $this->get('hopitalnumerique_autodiag.manager.chapitre')->getReferences($chapitre, $results);

        $titre = $chapitre->getTitle();
        if( $chapitre->getCode() != '' )
        {
            $titre = $chapitre->getCode() . '. ' . $chapitre->getTitle();
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Reference:manage.html.twig', array(
            'references' => $references,
            'chapitre'   => $chapitre->getId(),
            'question'   => 'null',
            'titre'      => $titre
        ));
    }

    /**
     * Enregistre les références du chapitre
     *
     * @param Chapitre $chapitre Le chapitre
     */
    public function saveChapitreAction( Chapitre $chapitre )
    {
        //efface toutes les anciennes références
        $oldRefs = $this->get('hopitalnumerique_autodiag.manager.refchapitre')->findBy( array('chapitre' => $chapitre) );
        $this->get('hopitalnumerique_autodiag.manager.refchapitre')->delete( $oldRefs );

        //ajoute les nouvelles références
        $references = json_decode( $this->get('request')->request->get('references') );
        $refToSave  = array();
        foreach( $references as $reference ) {
            $ref = $this->get('hopitalnumerique_autodiag.manager.refchapitre')->createEmpty();
            $ref->setChapitre( $chapitre );
            $ref->setPrimary( $reference->type );

            //get ref
            $ref->setReference( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $reference->id) ) );

            //save it
            $refToSave[] = $ref;
        }
        $this->get('hopitalnumerique_autodiag.manager.refchapitre')->save( $refToSave );

        //get Chapitre Note
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees($chapitre->getOutil()->getDomainesId());
        $note = $this->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $chapitre->getReferences(), $refsPonderees );

        return new Response('{"success":true, "note":"' . number_format($note, 2, ',', ' ') . '"}', 200);
    }

    /**
     * Affiche les références des questions
     *
     * @param Question $question La question
     */
    public function questionAction( Question $question )
    {
        //get references and selected references as One Array
        $results    = $this->get('hopitalnumerique_reference.manager.reference')->getRefsForGestionObjets();
        $references = $this->get('hopitalnumerique_autodiag.manager.question')->getReferences($question, $results);

        $titre = $question->getTexte();
        if( $question->getCode() != '' )
            $titre = $question->getCode() . '. ' . $question->getTexte();

        return $this->render('HopitalNumeriqueAutodiagBundle:Reference:manage.html.twig', array(
            'references' => $references,
            'chapitre'   => 'null',
            'question'   => $question->getId(),
            'titre'      => $titre
        ));
    }

    /**
     * Enregistre les références de la question
     *
     * @param Question $question La question
     */
    public function saveQuestionAction( Question $question )
    {
        //efface toutes les anciennes références
        $oldRefs = $this->get('hopitalnumerique_autodiag.manager.refquestion')->findBy( array('question' => $question) );
        $this->get('hopitalnumerique_autodiag.manager.refquestion')->delete( $oldRefs );

        //ajoute les nouvelles références
        $references = json_decode( $this->get('request')->request->get('references') );
        $refToSave  = array();
        foreach( $references as $reference ) {
            $ref = $this->get('hopitalnumerique_autodiag.manager.refquestion')->createEmpty();
            $ref->setQuestion( $question );
            $ref->setPrimary( $reference->type );

            //get ref
            $ref->setReference( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $reference->id) ) );

            //save it
            $refToSave[] = $ref;
        }
        $this->get('hopitalnumerique_autodiag.manager.refquestion')->save( $refToSave );

        //get question Note
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees($question->getChapitre()->getOutil()->getDomainesId());
        $note = $this->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $question->getReferences(), $refsPonderees );

        return new Response('{"success":true, "note":"' . number_format($note, 2, ',', ' ') . '"}', 200);
    }


}