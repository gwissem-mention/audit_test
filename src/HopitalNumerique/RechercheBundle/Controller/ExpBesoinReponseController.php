<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use HopitalNumerique\RechercheBundle\Entity\ExpBesoin;
use HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses;

class ExpBesoinReponseController extends Controller
{
    public function indexAction(ExpBesoin $expBesoin)
    {
        $expBesoins = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findAll();

        $notes = array();

        foreach ($expBesoin->getReponses() as $reponse) 
        {
            //get ponderations
            $refsPonderees = $this->container->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
            $note          = $this->container->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $reponse->getReferences(), $refsPonderees );

            $notes[$reponse->getId()] = $note;
        }

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:index.html.twig', array(
                'expBesoinAll'  => $expBesoins,
                'expBesoin'     => $expBesoin,
                'notes'         => $notes
            ));    
    }

    public function addAction(Request $request)
    {
        //créer un question
        $reponse = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->createEmpty();

        $libelle          = $request->request->get('libelle');
        $redirigeQuestion = $request->request->get('redirigeQuestion');
        $question         = $request->request->get('question');
        $autreQuestion    = $request->request->get('autreQuestion');

        //Calcul de l'ordre
        $order            = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->countReponses($question) + 1;
        $question         = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findOneBy(array('id' => $question));
        $redirigeQuestion = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findOneBy(array('id' => $redirigeQuestion));

        $reponse->setOrder( $order );
        $reponse->setLibelle( $libelle );
        $reponse->setRedirigeQuestion( $redirigeQuestion );
        $reponse->setAutreQuestion( $autreQuestion == 'true' ? true : false );
        $reponse->setQuestion( $question );

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->save( $reponse );

        return new Response('{"success":true}', 200);   
    }

    public function editAction(Request $request)
    {
        //Récupération des données envoyées par la requete AJAX
        $idReponse       = $request->request->get('idReponse');
        $isAutreQuestion = $request->request->get('isAutreQuestion') == 'true' ? true : false;
        //Modification de la réponse
        $reponse = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->findOneBy(array('id' => $idReponse));

        if($isAutreQuestion)
        {
            //efface toutes les références
            $oldRefs = $this->get('hopitalnumerique_recherche.manager.refexpbesoinreponses')->findBy( array('expBesoinReponses' => $idReponse) );
            $this->get('hopitalnumerique_recherche.manager.refexpbesoinreponses')->delete( $oldRefs );

            //Set la question
            $idQuestion = $request->request->get('idQuestion');

            $question   = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findOneBy(array('id' => $idQuestion));
            $reponse->setRedirigeQuestion($question);
        }

        $reponse->setAutreQuestion( $isAutreQuestion );

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->save( $reponse );

        return new Response('{"success":true}', 200);   
    }

    public function deleteAction(Request $request)
    {
        //Récupération des données envoyées par la requete AJAX
        $idReponse       = $request->request->get('id');
        $reponse = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->findOneBy(array('id' => $idReponse));

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->delete( $reponse );

        return new Response('{"success":true}', 200); 
    }

    public function saveAction()
    {
        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:save.html.twig', array(
                // ...
            ));    
    }

    public function reorderAction()
    {
        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:reorder.html.twig', array(
                // ...
            ));    
    }
    /**
     * [topicAction description]
     *
     * @param  ExpBesoinReponses  $topic [description]
     *
     * @return [type]
     */
    public function referenceAction( ExpBesoinReponses $expBesoinReponses )
    {
        //get references and selected references as One Array
        $results    = $this->get('hopitalnumerique_reference.manager.reference')->getRefsForGestionObjets();
        $references = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->getReferences($expBesoinReponses, $results);

        return $this->render('HopitalNumeriqueRechercheBundle:RefExpBesoinReponses:manage.html.twig', array(
            'references'        => $references,
            'topic'             => true,
            'contenu'           => 'null',
            'titre'             => $expBesoinReponses->getQuestion()->getLibelle(),
            'expBesoinReponses' => $expBesoinReponses
        ));
    }

    /**
     * [saveExpBesoinReponsesAction description]
     *
     * @param  ExpBesoinReponses  $topic [description]
     *
     * @return [type]
     */
    public function referenceSaveAction( ExpBesoinReponses $expBesoinReponses )
    {
        //efface toutes les anciennes références
        $oldRefs = $this->get('hopitalnumerique_recherche.manager.refexpbesoinreponses')->findBy( array('expBesoinReponses' => $expBesoinReponses) );
        $this->get('hopitalnumerique_recherche.manager.refexpbesoinreponses')->delete( $oldRefs );

        //ajoute les nouvelles références
        $references = json_decode( $this->get('request')->request->get('references') );
        $refToAdd   = array();
        foreach( $references as $reference )
        {
            $ref = $this->get('hopitalnumerique_recherche.manager.refexpbesoinreponses')->createEmpty();
            $ref->setExpBesoinReponses( $expBesoinReponses );
            $ref->setPrimary( $reference->type );

            //get ref
            $ref->setReference( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $reference->id) ) );

            //save it
            $refToAdd[] = $ref;
        }

        $this->get('hopitalnumerique_recherche.manager.refexpbesoinreponses')->save( $refToAdd );

        //get ExpBesoinReponses Note
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $note = $this->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $expBesoinReponses->getReferences(), $refsPonderees );

        return new Response('{"success":true, "note":'.$note.'}', 200);
    }

}
