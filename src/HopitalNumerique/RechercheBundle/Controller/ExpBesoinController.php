<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use HopitalNumerique\RechercheBundle\Entity\ExpBesoin;
use HopitalNumerique\RechercheBundle\Entity\ExpBesoinGestion;

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

    /**
     * Édite l'entité (appel AJAX).
     *
     * @param \Symfony\Component\HttpFoundation\Request          $request   Request
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoin $expBesoin ExpBesoin
     */
    public function editAction(Request $request, ExpBesoin $expBesoin)
    {
        $form = $this->createForm('hopitalnumerique_recherche_expbesoin', $expBesoin);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $field) {
                    foreach ($field->getErrors() as $fieldError) {
                        $this->addFlash('danger', $fieldError->getMessage());
                    }
                }
            } else {
                $this->container->get('hopitalnumerique_recherche.manager.expbesoin')->save($expBesoin);
                $this->addFlash('success', 'Image enregistrée.');
            }

            return $this->redirectToRoute('hopital_numerique_expbesoin_index', ['id' => $expBesoin->getExpBesoinGestion()->getId()]);
        }

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoin:edit.html.twig', [
            'form' => $form->createView(),
            'expBesoin' => $expBesoin
        ]);
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
     * .
     */
    public function modificationSessionAction(Request $request)
    {
        $entityHasReferences = $this->container->get('hopitalnumerique_reference.manager.entity_has_reference')->findBy([
            'entityId' => $request->request->getInt('id'),
            'entityType' => Entity::ENTITY_TYPE_RECHERCHE_PARCOURS
        ]);

        $referenceIds = [];
        foreach ($entityHasReferences as $entityHasReference) {
            $referenceIds[] = $entityHasReference->getReference()->getId();
        }

        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->remove();
        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setReferenceIds($referenceIds);

        return new JsonResponse(['success' => true]);
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
