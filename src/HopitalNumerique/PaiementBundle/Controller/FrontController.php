<?php

namespace HopitalNumerique\PaiementBundle\Controller;

use HopitalNumerique\PaiementBundle\Entity\Facture;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpFoundation\Response;

/**
 * Front controller.
 */
class FrontController extends Controller
{
    /**
     * Interface de suivi des paiements en front.
     *
     * @return RedirectResponse|Response
     */
    public function suiviAction()
    {
        //On récupère l'user connecté
        $user = $this->getUser();

        if (is_null($user->getRegion())) {
            $this->addFlash('warning', 'Merci de saisir votre région avant d\'accéder à l\'interface de suivi des paiements.');

            return $this->redirectToRoute('hopital_numerique_user_informations_personnelles');
        }

        //get interventions + formations
        $interventions = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getForFactures($user);
        $formations = $this->get('hopitalnumerique_module.manager.inscription')->getForFactures($user);

        $datas = $this->get('hopitalnumerique_paiement.manager.remboursement')->calculPrice($interventions, $formations);

        $canGenererFacture = $this->container->get('hopitalnumerique_paiement.manager.facture')->canGenererFacture($interventions);
        if (!$canGenererFacture) {
            $this->addFlash('warning', 'L\'édition de facture n\'est pas possible (les montants affichés sont incorrects). L\'ANAP a été prévenue. Répétez l\'opération d\'ici quelques jours.');
        }

        //get Factures
        $factures = $this->get('hopitalnumerique_paiement.manager.facture')->getFacturesOrdered($user);

        return $this->render('HopitalNumeriquePaiementBundle:Front:suivi.html.twig', [
            'datas' => $datas,
            'factures' => $factures,
            'canGenererFacture' => $canGenererFacture,
        ]);
    }

    /**
     * Enregistre la facture.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function createFactureAction(Request $request)
    {
        $interventions = $request->request->get('intervention');
        $formations = $request->request->get('formation');

        //On récupère l'user connecté
        $user = $this->getUser();

        if (!$this->get('hopitalnumerique_module.manager.inscription')->allInscriptionsIsOk($user)) {
            $this->addFlash('warning', 'Merci d\'évaluer l\'ensemble de vos formations avant de générer votre facture.');

            return new RedirectResponse($this->generateUrl('account_profile').'#?payment');
        }

        if (is_null($interventions) && is_null($formations)) {
            $this->addFlash('warning', 'Merci de sélectioner au moins 1 ligne');

            return new RedirectResponse($this->generateUrl('account_profile').'#?payment');
        }

        $remboursement = $this->get('hopitalnumerique_paiement.manager.remboursement')->findOneBy(['region' => $user->getRegion()]);
        $facture = $this->get('hopitalnumerique_paiement.manager.facture')->createFacture($user, $interventions, $formations, $remboursement->getSupplement());

        //get Reponses
        $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser(2, $user->getId(), true);
        $infos = $this->get('hopitalnumerique_paiement.manager.facture')->formateInfos($reponses);

        //Generate PDF
        $code = $facture->getUser()->getId() . $facture->getId();
        $this->get('knp_snappy.pdf')->generateFromHtml(
            $this->renderView('HopitalNumeriquePaiementBundle:Pdf:facture.html.twig', [
                'code' => $code,
                'facture' => $facture,
                'infos' => $infos,
                'formations' => [],
            ]),
            __ROOT_DIRECTORY__ . '/files/factures/facture' . $code . '.pdf',
            ['margin-bottom' => 10, 'margin-left' => 4, 'margin-right' => 4, 'margin-top' => 10, 'encoding' => 'UTF-8']
        );

        //save file Name
        $facture->setName('facture' . $code . '.pdf');
        $this->get('hopitalnumerique_paiement.manager.facture')->save($facture);

        $this->addFlash('success', 'Facture générée avec succès');

        return new RedirectResponse($this->generateUrl('account_profile').'#?payment');
    }

    /**
     * Exporte la facture.
     *
     * @param Facture $facture
     *
     * @return RedirectResponse|Response
     */
    public function exportAction(Facture $facture)
    {
        $options = [
            'serve_filename' => $facture->getName(),
            'absolute_path' => false,
            'inline' => false,
        ];

        $fileName = __ROOT_DIRECTORY__ . '/files/factures/' . $facture->getName();

        if (file_exists($fileName)) {
            return $this->get('igorw_file_serve.response_factory')->create($fileName, 'application/pdf', $options);
        } else {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add(('danger'), 'Le document n\'existe plus sur le serveur.');

            return new RedirectResponse($this->generateUrl('account_profile').'#?payment');
        }
    }
}
