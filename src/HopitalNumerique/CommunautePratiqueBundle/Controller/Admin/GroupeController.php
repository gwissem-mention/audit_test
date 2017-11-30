<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

/**
 * Contrôleur des groupes dans l'admin.
 */
class GroupeController extends Controller
{
    /**
     * Affiche les groupes à gérer.
     *
     * @return Response
     */
    public function listAction()
    {
        $groupeGrid = $this->get('hopitalnumerique_communautepratique.grid.groupe');

        return $groupeGrid->render('HopitalNumeriqueCommunautePratiqueBundle:Admin/Groupe:list.html.twig');
    }

    /**
     * Ajoute un groupe.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function addAction(Request $request)
    {
        /** @var Groupe $nouveauGroupe */
        $nouveauGroupe = $this->get('hopitalnumerique_communautepratique.manager.groupe')->createEmpty();
        $nouveauGroupe->setIsNew(true);

        if ($request->query->has('domains')) {

            $domains = $this->get('hopitalnumerique_domaine.repository.domaine')->findById(explode('-', $request->query->get('domains')));
            foreach ($domains as $domain) {
                $nouveauGroupe->addDomain($domain);
            }
        } elseif ($request->request->has('hopitalnumerique_communautepratiquebundle_groupe')) {
            $formPost = $request->request->get('hopitalnumerique_communautepratiquebundle_groupe');


            return $this->redirect(
                $this->generateUrl(
                    'hopitalnumerique_communautepratique_admin_groupe_add',
                    ['domains' => implode('-', $formPost['domains'])]
                )
            );
        }

        return $this->editAction($request, $nouveauGroupe);
    }

    /**
     * Édite un groupe.
     *
     * @param Request  $request
     * @param Groupe   $groupe
     * @param int      $toRef
     *
     * @return Response
     */
    public function editAction(Request $request, Groupe $groupe, $toRef = 0)
    {
        $groupeForm = $this->createForm('hopitalnumerique_communautepratiquebundle_groupe', $groupe);
        $groupeForm->handleRequest($request);

        if ($groupeForm->isSubmitted()) {
            if ($groupeForm->isValid()) {
                $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->save($groupe);
                $this->get('session')->getFlashBag()->add('success', 'Groupe enregistré.');
                $do = $this->container->get('request')->request->get('do');

                return $this->redirect(
                    $do == 'save-close'
                        ? $this->generateUrl('hopitalnumerique_communautepratique_admin_groupe_list')
                        : $this->generateUrl(
                            'hopitalnumerique_communautepratique_admin_groupe_edit',
                            ['id' => $groupe->getId()]
                        )
                );
            } else {
                $this->get('session')->getFlashBag()->add('danger', 'Groupe non enregistré.');
            }
        }

        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Admin/Groupe:edit.html.twig',
            [
                'groupeForm' => $groupeForm->createView(),
                'groupe' => $groupe,
                'toRef' => (int) $toRef,
            ]
        );
    }

    /**
     * Supprime en masse les groupes.
     *
     * @param array $primaryKeys
     *
     * @return RedirectResponse
     */
    public function deleteMassAction(array $primaryKeys)
    {
        $utilisateurConnecte = $this->getUser();

        if ($this->get('nodevo_acl.manager.acl')->checkAuthorization(
            $this->generateUrl('hopitalnumerique_communautepratique_admin_groupe_deletemass'),
            $utilisateurConnecte
        ) != -1) {
            $groupes = $this->get('hopitalnumerique_communautepratique.manager.groupe')->findBy(['id' => $primaryKeys]);
            $this->get('hopitalnumerique_communautepratique.manager.groupe')->delete($groupes);

            $this->addFlash('info', 'Suppression effectuée avec succès.');
        } else {
            $this->addFlash('warning', 'Vous ne possédez pas les droits nécessaires pour supprimer des groupes.');
        }

        return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_admin_groupe_list'));
    }
}
