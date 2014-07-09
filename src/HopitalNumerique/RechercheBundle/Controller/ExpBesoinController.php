<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use HopitalNumerique\RechercheBundle\Entity\ExpBesoin;

class ExpBesoinController extends Controller
{
    public function indexAction()
    {
        $expBesoins = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findAll();

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoin:index.html.twig', array(
                'expBesoins' => $expBesoins
            ));    

    }

    /**
     * Ajoute une question
     */
    public function addQuestionAction(Request $request)
    {
        //créer un question
        $question = $this->get('hopitalnumerique_recherche.manager.expbesoin')->createEmpty();

        //Calcul de l'ordre
        $order = $this->get('hopitalnumerique_recherche.manager.expbesoin')->countQuestions() + 1;
        $titre = trim($request->request->get('titre')) ? : 'Question '.$order;

        $question->setOrder( $order );
        $question->setLibelle( $titre );

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoin')->save( $question );

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoin:add.html.twig', array(
            'expBesoin' => $question
        ));
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



}
