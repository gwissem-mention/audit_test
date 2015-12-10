<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use HopitalNumerique\RechercheBundle\Entity\ExpBesoin;
use HopitalNumerique\RechercheBundle\Entity\ExpBesoinGestion;
use HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses;

class ExpBesoinController extends Controller
{
    public function indexAction(ExpBesoinGestion $expBesoinGestion)
    {
        $expBesoins = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findBy(array('expBesoinGestion' => $expBesoinGestion), array('order' => 'ASC'));

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoin:index.html.twig', array(
                'expBesoins'       => $expBesoins,
                'expBesoinGestion' => $expBesoinGestion
            ));
    }

    public function addQuestionAction(Request $request, ExpBesoinGestion $expBesoinGestion )
    {
        //créer un question
        $question = $this->get('hopitalnumerique_recherche.manager.expbesoin')->createEmpty();

        //Calcul de l'ordre
        $order = $this->get('hopitalnumerique_recherche.manager.expbesoin')->countQuestions() + 1;
        $titre = trim($request->request->get('titre')) ? : 'Question '.$order;

        $question->setOrder( $order );
        $question->setLibelle( $titre );
        $question->setExpBesoinGestion($expBesoinGestion);

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoin')->save( $question );

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoin:add.html.twig', array(
            'expBesoin' => $question
        ));
    }


    public function descriptionAction( $id )
    {
        $expBesoin  = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findOneBy(array('id' => $id));

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoin:addDescription.html.twig', array(
            'expBesoin'      => $expBesoin
        ));
    }


    public function descriptionSaveAction(Request $request)
    {
        //Calcul de l'ordre
        $id          = $request->request->get('id');
        $description = $request->request->get('description');

        $expBesoin  = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findOneBy(array('id' => $id));

        $expBesoin->setDescription( $description );

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoin')->save( $expBesoin );

        return new Response('{"success":true}', 200);
    }

    /**
     * Edite une question
     */
    public function editQuestionAction(Request $request)
    {
        $idExpBesoin = $request->request->get('id');
        //créer un question
        $expBesoin = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findOneBy(array('id' => $idExpBesoin));

        $expBesoin->setLibelle( trim($request->request->get('titre')) ? : 'Question '.$order );

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoin')->save( $expBesoin );

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoin:add.html.twig', array(
            'expBesoin' => $expBesoin
        ));
    }

    /**
     * Suppresion d'un chapitre.
     *
     * METHOD = POST|DELETE
     */
    public function deleteAction( ExpBesoin $expBesoin )
    {
        //delete
        $this->get('hopitalnumerique_recherche.manager.expbesoin')->delete( $expBesoin );

        return new Response('{"success":true}', 200);
    }

    /**
     * Met à jour l'ordre des différentes questions
     */
    public function reorderAction()
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_recherche.manager.expbesoin')->reorder( $datas );
        $this->getDoctrine()->getManager()->flush();

        return new Response('{"success":true}', 200);
    }

    /**
     * Met à jour l'ordre des différentes questions
     */
    public function modificationSessionAction(Request $request)
    {
        $idExpBesoinReponses = $request->request->get('id');

        $expBesoinReponses = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->findOneBy(array('id' => $idExpBesoinReponses));

        //Création du tableau pour la session de recherche
        $resultats = array(
            'categ1' => array(),
            'categ2' => array(),
            'categ3' => array(),
            'categ4' => array()
        );

        //Parcourt les références de la réponse, puis les tris pour l'affichage de la recherche
        foreach ($expBesoinReponses->getReferences() as $refExpBesoinReponses) 
        {
            //Récupère la référence courante
            $reference     = $refExpBesoinReponses->getReference();
            $referenceTemp = $reference;

            //Récupère le premier parent
            while(!is_null($referenceTemp->getParent())
                    && $referenceTemp->getParent()->getId() != null)
            {
                $referenceTemp = $referenceTemp->getParent();
            }

            //Trie la référence dans la bonne catégorie
            switch ($referenceTemp->getId()) 
            {
                case 220:
                    $resultats['categ1'][] = $reference->getId();
                    break;
                case 221:
                    $resultats['categ2'][] = $reference->getId();
                    break;
                case 223:
                    $resultats['categ3'][] = $reference->getId();
                    break;
                case 222:
                    $resultats['categ4'][] = $reference->getId();
                    break;
            }
        }

        //on prépare la session
        $session = $this->getRequest()->getSession();
        $session->set('requete-id', null);
        $session->set('requete-refs', json_encode($resultats) );
        $session->set('requete-refs-categProd', '' );

        //return success.true si le fichier existe deja
        return new Response('{"success":true}', 200);
    }


    //**********************
    //**** FRONT OFFICE ****
    //**********************

    /**
     * POPIN : Recherche en Front de l'aide à l'expression du besoin
     */
    public function rechercheAction(ExpBesoinGestion $expBesoinGestion)
    {
        $expBesoins = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findBy(array('expBesoinGestion' => $expBesoinGestion), array('order' => 'ASC'));
        $reponses = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->getAllReponsesInArrayById();

        return $this->render( 'HopitalNumeriqueRechercheBundle:ExpBesoin:Fancy/fancy_front.html.twig' , array(
            'expBesoins' => $expBesoins,
            'reponses'   => $reponses
        ));
    }
    /**
     * POPIN : Recherche en Front de l'aide à l'expression du besoin
     */
    public function rechercheNoPopinAction(ExpBesoinGestion $expBesoinGestion)
    {
        $expBesoins = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findBy(array('expBesoinGestion' => $expBesoinGestion), array('order' => 'ASC'));
        $reponses = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->getAllReponsesInArrayById();

        return $this->render( 'HopitalNumeriqueRechercheBundle:ExpBesoin:Fancy/nopoppin_front.html.twig' , array(
            'expBesoins' => $expBesoins,
            'reponses'   => $reponses
        ));
    }
}
