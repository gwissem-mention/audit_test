<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\RechercheBundle\Entity\ExpBesoin;
use HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses;

/**
 * Class ExpBesoinReponseController
 */
class ExpBesoinReponseController extends Controller
{
    /**
     * @param ExpBesoin $expBesoin
     *
     * @return Response
     */
    public function indexAction(ExpBesoin $expBesoin)
    {
        $expBesoins = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findBy(
            ['expBesoinGestion' => $expBesoin->getExpBesoinGestion()],
            ['order' => 'ASC']
        );

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:index.html.twig', [
                'expBesoinAll' => $expBesoins,
                'expBesoin' => $expBesoin,
            ]);
    }

    /**
     * @param Request   $request
     * @param ExpBesoin $expBesoin
     *
     * @return Response
     */
    public function addAction(Request $request, ExpBesoin $expBesoin)
    {
        $expBesoins = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findBy(
            ['expBesoinGestion' => $expBesoin->getExpBesoinGestion()],
            ['order' => 'ASC']
        );

        //créer un question
        /** @var ExpBesoinReponses $reponse */
        $reponse = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->createEmpty();

        $libelle = $request->request->get('libelle');
        $redirigeQuestion = $request->request->get('redirigeQuestion');
        $question = $request->request->get('question');
        $autreQuestion = $request->request->get('autreQuestion');

        //Calcul de l'ordre
        $order = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->countReponses($question) + 1;
        $question = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findOneBy(['id' => $question]);
        $redirigeQuestion = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findOneBy(
            ['id' => $redirigeQuestion]
        );

        $reponse->setOrder($order);
        $reponse->setLibelle($libelle);
        if ($redirigeQuestion != null) {
            $reponse->setRedirigeQuestion($redirigeQuestion);
        }
        $reponse->setAutreQuestion($autreQuestion == 'true' ? true : false);
        $reponse->setQuestion($question);

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->save($reponse);

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:add.html.twig', [
            'reponse' => $reponse,
            'expBesoin' => $question,
            'expBesoinAll' => $expBesoins,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request)
    {
        //Récupération des données envoyées par la requete AJAX
        $idReponse = $request->request->get('idReponse');
        $isAutreQuestion = $request->request->get('isAutreQuestion') == 'true' ? true : false;
        //Modification de la réponse
        $reponse = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->findOneBy(['id' => $idReponse]);

        $questionId = $reponse->getQuestion()->getId();

        if ($isAutreQuestion) {
            //Set la question
            $idQuestion = $request->request->get('idQuestion');

            $question = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findOneBy(['id' => $idQuestion]);
            $reponse->setRedirigeQuestion($question);
        }

        $reponse->setAutreQuestion($isAutreQuestion);

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->save($reponse);

        return new Response('{"success":true, "questionId: '. $questionId . '}', 200);
    }

    /**
     * Édite la réponse (appel AJAX).
     *
     * @param Request           $request          Request
     * @param ExpBesoinReponses $expBesoinReponse Réponse
     *
     * @return RedirectResponse|Response
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

            return $this->redirectToRoute(
                'hopital_numerique_expbesoin_index',
                ['id' => $expBesoinReponse->getQuestion()->getExpBesoinGestion()->getId()]
            );
        }

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinReponse:ajaxEdit.html.twig', [
            'form' => $form->createView(),
            'expBesoinReponse' => $expBesoinReponse,
        ]);
    }

    /**
     * Edit libellé d'une réponse.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editLibelleAction(Request $request)
    {
        $idReponse = $request->request->get('id');

        //créer un question

        /** @var ExpBesoinReponses $reponse */
        $reponse = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->findOneBy(['id' => $idReponse]);

        $questionId = $reponse->getQuestion()->getId();

        $reponse->setLibelle(trim($request->request->get('titre')) ?: 'Réponse ' . $reponse->getOrder());

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->save($reponse);

        return new Response('{"success":true, "questionId": '. $questionId . '}', 200);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        //Récupération des données envoyées par la requete AJAX
        $idReponse = $request->request->get('id');
        $reponse = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->findOneBy(['id' => $idReponse]);

        $questionId = $reponse->getQuestion()->getId();

        //save
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->delete($reponse);

        return new Response('{"success":true, "questionId": '. $questionId . '}', 200);
    }

    /**
     * @return Response
     */
    public function reorderAction()
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->reorder($datas, null);
        $this->getDoctrine()->getManager()->flush();

        //return success.true si le fichier existe deja
        return new Response('{"success":true}', 200);
    }
}
