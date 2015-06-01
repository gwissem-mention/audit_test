<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Reference controller.
 */
class ReferenceController extends Controller
{
    /**
     * [objetAction description]
     *
     * @param  [type] $id [description]
     *
     * @return [type]
     */
    public function objetAction( $id )
    {
        //Récupération de l'objet passée en paramètre
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        //get references and selected references as One Array
        $results    = $this->get('hopitalnumerique_reference.manager.reference')->getRefsForGestionObjets();
        $references = $this->get('hopitalnumerique_objet.manager.objet')->getReferences($objet, $results);

        return $this->render('HopitalNumeriqueObjetBundle:Reference:manage.html.twig', array(
            'references' => $references,
            'objet'      => true,
            'contenu'    => 'null',
            'titre'      => $objet->getTitre()
        ));
    }
    
    /**
     * [objetOwnAction description]
     *
     * @param  [type] $id [description]
     *
     * @return [type]
     */
    public function objetOwnAction( $id )
    {
        //Récupération de l'objet passée en paramètre
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        //get references and selected references as One Array
        $references = $this->get('hopitalnumerique_objet.manager.objet')->getReferencesOwn($objet);

        return $this->render('HopitalNumeriqueObjetBundle:Reference:manage-own.html.twig', array(
            'references' => $references,
            'objet'      => true,
            'contenu'    => 'null'
        ));
    }

    /**
     * [contenuAction description]
     *
     * @param  [type] $id [description]
     *
     * @return [type]
     */
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

    /**
     * [contenuOwnAction description]
     *
     * @param  [type] $id [description]
     *
     * @return [type]
     */
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

    /**
     * [saveObjetAction description]
     *
     * @param  [type] $id [description]
     *
     * @return [type]
     */
    public function saveObjetAction( $id )
    {
        //Récupération de l'objet passée en paramètre
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );
        
        //efface toutes les anciennes références
        $oldRefs = $this->get('hopitalnumerique_objet.manager.refobjet')->findBy( array('objet' => $objet) );
        $this->get('hopitalnumerique_objet.manager.refobjet')->delete( $oldRefs );
        $objet->setReferencement( array() );

        //ajoute les nouvelles références
        $references = json_decode( $this->get('request')->request->get('references') );
        $refsToSave = array();
        foreach( $references as $reference ) {
            $ref = $this->get('hopitalnumerique_objet.manager.refobjet')->createEmpty();
            $ref->setObjet( $objet );
            $ref->setPrimary( $reference->type );

            //get ref
            $itemRef = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $reference->id) );
            $ref->setReference( $itemRef );

            //update Objet
            $objet->addReferencement( $itemRef->getLibelle() . ' : ' . ($reference->type ? 'Primaire' : 'Secondaire') );

            //save it
            $refsToSave[] = $ref;
        }

        //save Refs AND objet (implicite)
        $this->get('hopitalnumerique_objet.manager.refobjet')->save( $refsToSave );

        //get Object Note
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees($objet->getDomainesId());
        $note = $this->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $objet->getReferences(), $refsPonderees );

        return new Response('{"success":true, "note":"' . number_format($note, 2, ',', ' ') . '"}', 200);
    }

    /**
     * [saveContenuAction description]
     *
     * @return [type]
     */
    public function saveContenuAction()
    {
        //on récupère le contenu
        $id      = $this->get('request')->request->get('contenu');
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array('id' => $id) );
        
        //efface toutes les anciennes références
        $oldRefs = $this->get('hopitalnumerique_objet.manager.refcontenu')->findBy( array('contenu' => $contenu) );
        $this->get('hopitalnumerique_objet.manager.refcontenu')->delete( $oldRefs );

        //ajoute les nouvelles références
        $references = json_decode( $this->get('request')->request->get('references') );
        $refsToSave = array();
        foreach( $references as $reference ) {
            $ref = $this->get('hopitalnumerique_objet.manager.refcontenu')->createEmpty();
            $ref->setContenu( $contenu );
            $ref->setPrimary( $reference->type );

            //get ref
            $ref->setReference( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $reference->id) ) );

            //save it
            $refsToSave[] = $ref;
        }
        $this->get('hopitalnumerique_objet.manager.refcontenu')->save( $refsToSave );

        //get Object Note
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $note = $this->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $contenu->getReferences(), $refsPonderees );

        return new Response('{"success":true, "note":"' . number_format($note, 2, ',', ' ') . '"}', 200);
    }
}