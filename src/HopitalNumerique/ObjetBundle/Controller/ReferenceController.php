<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Reference controller.
 */
class ReferenceController extends Controller
{
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
            'contenu'    => 'null'
        ));
    }
    
    public function objetOwnAction( $id )
    {
        //Récupération de l'objet passée en paramètre
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        //get references and selected references as One Array
//        $results    = $this->get('hopitalnumerique_reference.manager.reference')->getRefsForGestionObjets();
        $references = $this->get('hopitalnumerique_objet.manager.objet')->getReferencesOwn($objet);
        return $this->render('HopitalNumeriqueObjetBundle:Reference:manage-own.html.twig', array(
            'references' => $references,
            'objet'      => true,
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
            'contenu'    => $id
        ));
    }





    public function saveObjetAction( $id )
    {
        //Récupération de l'objet passée en paramètre
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );
        
        //efface toutes les anciennes références
        $oldRefs = $this->get('hopitalnumerique_objet.manager.refobjet')->findBy( array('objet' => $objet) );
        $this->get('hopitalnumerique_objet.manager.refobjet')->delete( $oldRefs );

        //ajoute les nouvelles références
        $nbRef      = 0;
        $references = json_decode( $this->get('request')->request->get('references') );
        foreach( $references as $reference ) {
            $ref = $this->get('hopitalnumerique_objet.manager.refobjet')->createEmpty();
            $ref->setObjet( $objet );
            $ref->setPrimary( $reference->type );

            //get ref
            $ref->setReference( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $reference->id) ) );

            //save it
            $this->get('hopitalnumerique_objet.manager.refobjet')->save( $ref );

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