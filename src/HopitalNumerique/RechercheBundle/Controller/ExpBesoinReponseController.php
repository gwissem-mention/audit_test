<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use HopitalNumerique\RechercheBundle\Entity\ExpBesoin;
use HopitalNumerique\RechercheBundle\Entity\ExpBesoinGestion;
use HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses;

class ExpBesoinReponseController extends Controller
{
    public function indexAction(ExpBesoin $expBesoin)
    {
        $expBesoins = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findBy(array('expBesoinGestion' => $expBesoin->getExpBesoinGestion()), array('order' => 'ASC'));

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:index.html.twig', array(
                'expBesoinAll'  => $expBesoins,
                'expBesoin'     => $expBesoin
            ));    
    }

    public function addAction(Request $request, ExpBesoin $expBesoin)
    {
        $expBesoins = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findBy(array('expBesoinGestion' => $expBesoin->getExpBesoinGestion()), array('order' => 'ASC'));

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
        if($redirigeQuestion != null)
            $reponse->setRedirigeQuestion( $redirigeQuestion );
        $reponse->setAutreQuestion( $autreQuestion == 'true' ? true : false );
        $reponse->setQuestion( $question );

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->save( $reponse );

        $notes = array();

        foreach ($question->getReponses() as $reponseQuestion) 
        {
            //get ponderations
            $refsPonderees = $this->container->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
            $note          = is_null($reponseQuestion->getReferences()) ? 0 : $this->container->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $reponseQuestion->getReferences(), $refsPonderees );

            $notes[$reponseQuestion->getId()] = $note;
        }

        //return new Response('{"success":true}', 200;
        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:add.html.twig', array(
            'reponse'      => $reponse,
            'expBesoin'    => $question,
            'expBesoinAll' => $expBesoins,
            'notes'        => $notes
        ));  
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

    /**
     * Édite la réponse (appel AJAX).
     *
     * @param \Symfony\Component\HttpFoundation\Request                  $request          Request
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $expBesoinReponse Réponse
     */
    public function ajaxEditAction(Request $request, ExpBesoinReponses $expBesoinReponse)
    {
        $form = $this->createForm('hopitalnumerique_recherche_expbesoinreponse', $expBesoinReponse);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $field) {
                    foreach ($field->getErrors() as $fieldError) {
                        $this->addFlash('danger', $fieldError->getMessage());
                    }
                }
            } else {
                $this->container->get('hopitalnumerique_recherche.manager.expbesoinreponses')->save($expBesoinReponse);
                $this->addFlash('success', 'Image enregistrée.');
            }

            return $this->redirectToRoute('hopital_numerique_expbesoin_index', ['id' => $expBesoinReponse->getQuestion()->getExpBesoinGestion()->getId()]);
        }

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:ajaxEdit.html.twig', [
            'form' => $form->createView(),
            'expBesoinReponse' => $expBesoinReponse
        ]);
    }


    /**
     * Edit libellé d'une réponse
     */
    public function editLibelleAction(Request $request)
    {
        $idReponse = $request->request->get('id');
        //créer un question
        $reponse = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->findOneBy(array('id' => $idReponse));

        $reponse->setLibelle( trim($request->request->get('titre')) ? : 'Réponse '.$order );

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

    public function reorderAction()
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->reorder( $datas, null );
        $this->getDoctrine()->getManager()->flush();

        //return success.true si le fichier existe deja
        return new Response('{"success":true}', 200);    
    }
}
