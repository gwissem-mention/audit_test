<?php

namespace HopitalNumerique\PaiementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Config controller.
 */
class ConfigController extends Controller
{
    /**
     * Page de configuration des prix.
     */
    public function indexAction()
    {
        $remboursements = $this->get('hopitalnumerique_paiement.manager.remboursement')->getRemboursementsOrdered();
        $usersPouvantEtreReferent = $this->container->get('hopitalnumerique_user.manager.user')->findUsersByRoles([
            'ROLE_ADMINISTRATEUR_1',
            'ROLE_ANAP_MEMBRES_2',
            'ROLE_ADMINISTRATEUR_DE_DOMAINE_106',
            'ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107',
        ]);

        return $this->render('HopitalNumeriquePaiementBundle:Config:index.html.twig', [
            'remboursements' => $remboursements,
            'usersPouvantEtreReferent' => $usersPouvantEtreReferent,
        ]);
    }

    /**
     * Sauvegarde le tableau de config.
     *
     * @param Request $request La requête
     *
     * @return Redirect
     */
    public function saveAction(Request $request)
    {
        //get posted Datas
        $remboursements = $request->request->get('remboursement');

        //prepare 1 save only
        $toSave = [];

        //run on each lines
        foreach ($remboursements as $id => $remboursement) {
            $entity = $this->get('hopitalnumerique_paiement.manager.remboursement')->findOneBy(['id' => $id]);
            $entity->setReferent($this->container->get('hopitalnumerique_user.manager.user')->findOneById(intval($remboursement['referent'])));
            $entity->setSupplement(trim($remboursement['supplement']) === '' ? null : intval($remboursement['supplement']));
            $entity->setRepas(intval($remboursement['repas']));
            $entity->setGestion(intval($remboursement['gestion']));

            $toSave[] = $entity;
        }
        $this->get('hopitalnumerique_paiement.manager.remboursement')->save($toSave);

        // On envoi une 'flash' pour indiquer à l'utilisateur la mise à jour
        $this->get('session')->getFlashBag()->add('info', 'Règles de calcul des remboursements mises à jour.');

        return $this->redirect($this->generateUrl('hopitalnumerique_paiement_config'));
    }
}
