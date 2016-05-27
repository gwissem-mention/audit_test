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
}