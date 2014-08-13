<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Link controller.
 */
class LinkController extends Controller
{
    /**
     * Fancybox d'ajout d'objet à l'utilisateur
     */
    public function addLinkAction( Objet $objet )
    {
        $types = $this->get('hopitalnumerique_reference.manager.reference')->findBy(array('code'=>'CATEGORIE_OBJET'));
        $arbo  = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsAndContenuArbo( $types );

        return $this->render('HopitalNumeriqueObjetBundle:Objet:add_link.html.twig', array(
            'arbo'    => $arbo,
            'idObjet' => $objet->getId()
        ));
    }

    /**
     * Sauvegarde le lien point dur -> objets
     */
    public function saveLinkAction()
    {
        //get posted vars
        $id     = $this->get('request')->request->get('idObjet');
        $objets = $this->get('request')->request->get('objets');

        //bind Objet
        $pointDur      = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );
        $currentObjets = new \Doctrine\Common\Collections\ArrayCollection($pointDur->getObjets());

        //bind objects
        foreach($objets as $one){
            if( !$currentObjets->contains($one) )
                $pointDur->addObjet( $one );
        }
        
        $this->get('hopitalnumerique_objet.manager.objet')->save( $pointDur );
        
        $this->get('session')->getFlashBag()->add( 'success' ,  'Les productions ont été liées au point dur.' );

        return new Response('{"success":true, "url" : "'. $this->generateUrl('hopitalnumerique_objet_objet_edit', array('id' => $id)).'"}', 200);
    }

    /**
     * Suppresion d'un lien point dur -> objet.
     *
     * METHOD = POST|DELETE
     */
    public function deleteLinkAction( Objet $pointDur, $id, $obj )
    {
        $objets = $pointDur->getObjets();

        $linkName = ($obj == 1 ? 'PUBLICATION' : 'INFRADOC') . ':' . $id;
        foreach($objets as $key => $objet){
            if( $objet == $linkName )
                unset($objets[$key]);
        }
        $pointDur->setObjets( $objets );
        $this->get('hopitalnumerique_objet.manager.objet')->save( $pointDur );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_objet_objet_edit', array('id' => $pointDur->getId())).'"}', 200);
    }

    /**
     * Reordonne les productions
     *
     * @param Objet $objet L'objet point dur
     *
     * @return Response
     */
    public function reorderAction( Objet $objet )
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');
        
        $doctrineArray = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($datas as $one)
            $doctrineArray->add( $one['id'] );

        $objet->setObjets( $doctrineArray->toArray() );
        $this->get('hopitalnumerique_objet.manager.objet')->save( $objet );
        
        //return success.true si le fichier existe deja
        return new Response('{"success":true}', 200);
    }
}