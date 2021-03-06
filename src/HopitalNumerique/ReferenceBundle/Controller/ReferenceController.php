<?php

namespace HopitalNumerique\ReferenceBundle\Controller;

use HopitalNumerique\ReferenceBundle\Domain\Command\SwitchReferenceCommand;
use HopitalNumerique\ReferenceBundle\Form\Type\SwitchReferenceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * Reference controller.
 */
class ReferenceController extends Controller
{
    /**
     * Affiche la liste des Reference.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_reference.grid.reference');

        return $grid->render('HopitalNumeriqueReferenceBundle:Reference:index.html.twig');
    }

    /**
     * Affichage en arborescence.
     */
    public function sitemapAction()
    {
        $referenceTree = $this->container->get('hopitalnumerique_reference.dependency_injection.reference.tree');

        $orderedRef = $referenceTree->getOrderedReferences(null, null, $this->getUser()->getDomaines());

        return $this->render('HopitalNumeriqueReferenceBundle:Reference:sitemap.html.twig', [
            'orderedReferences' => $orderedRef,
        ]);
    }

    /**
     * Affiche le formulaire d'ajout de Reference.
     *
     * @return RedirectResponse|Response
     */
    public function addAction()
    {
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->createEmpty();

        return $this->renderForm($reference);
    }

    /**
     * Affiche le formulaire d'édition de Reference.
     *
     * @param $id
     *
     * @return RedirectResponse|Response
     */
    public function editAction($id)
    {
        //Récupération de l'entité passée en paramètre
        $reference = $this->get('hopitalnumerique_reference.repository.reference')->findOneByIdWithJoin($id);

        if (null === $reference) {
            return $this->redirectToRoute('hopitalnumerique_reference_reference');
        }

        return $this->renderForm($reference);
    }

    /**
     * Sauvegarde les paramètres des activités d'expert.
     *
     * @param Request   $request
     * @param Reference $reference
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function saveReferenceAjaxAction(Request $request, Reference $reference)
    {
        $montant = $request->request->get('montant');
        $reference->setLibelle($montant);
        $this->get('hopitalnumerique_reference.manager.reference')->save($reference);

        $referenceManager = $this->container->get('hopitalnumerique_reference.manager.reference');

        $contratModele = $referenceManager->findOneByCode('ACTIVITE_EXPERT_CONTRAT_MODELE');
        if (null === $contratModele) {
            throw new \Exception('Référence "ACTIVITE_EXPERT_CONTRAT_MODELE" introuvable');
        }
        $contratModele->setLibelle($request->request->get('contratModele'));
        $this->get('hopitalnumerique_reference.manager.reference')->save($contratModele);

        $pvRecettesModele = $referenceManager->findOneByCode('ACTIVITE_EXPERT_PV_RECETTES_MODELE');
        if (null === $pvRecettesModele) {
            throw new \Exception('Référence "ACTIVITE_EXPERT_PV_RECETTES_MODELE" introuvable');
        }
        $pvRecettesModele->setLibelle($request->request->get('pvRecettesModele'));
        $this->get('hopitalnumerique_reference.manager.reference')->save($pvRecettesModele);

        $response = json_encode(['success' => true]);

        return new Response($response, 200);
    }

    /**
     * Affiche le Reference en fonction de son ID passé en paramètre.
     *
     * @param $id
     *
     * @return Response
     */
    public function showAction($id)
    {
        //Récupération de l'entité en fonction du paramètre
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(
            [
                'id' => $id,
            ]
        );

        return $this->render('HopitalNumeriqueReferenceBundle:Reference:show.html.twig', [
            'reference' => $reference,
        ]);
    }

    /**
     * Suppression d'un Reference.
     * METHOD = POST|DELETE.
     *
     * @param $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(
            [
                'id' => $id,
            ]
        );

        if ($reference->getLock()) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                'Suppression impossible, la référence est verrouillée.'
            )
            ;
        } else {
            //Tentative de suppression si la référence est liée nulle part
            try {
                //Suppression de l'entité
                $this->get('hopitalnumerique_reference.manager.reference')->delete($reference);
                $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    'Suppression impossible, la référence est actuellement liée et ne peut être supprimée.'
                );
            }
        }

        return new Response(
            '{"success":true, "url" : "' . $this->generateUrl('hopitalnumerique_reference_reference') . '"}',
            200
        );
    }

    /**
     * Suppression de masse des références.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return RedirectResponse
     */
    public function deleteMassAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_reference.manager.reference')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $references = $this->get('hopitalnumerique_reference.manager.reference')->findBy(
            [
                'id' => $primaryKeys,
            ]
        );

        $this->get('hopitalnumerique_reference.manager.reference')->delete($references);

        return $this->redirect(
            $this->generateUrl('hopitalnumerique_reference_reference')
        );
    }

    /**
     * Export CSV de la liste des références sélectionnés.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Response
     */
    public function exportCsvAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_reference.grid.reference')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }
        $refs = $this->get('hopitalnumerique_reference.manager.reference')->getDatasForExport($primaryKeys);

        $colonnes = [
            'id' => 'id',
            'libelle' => 'Libellé du concept',
            'sigle' => 'Sigle',
            'domaineNoms' => 'Domaine(s)',
            'reference' => 'Est une référence',
            'referenceLibelle' => 'Libellé de la référence',
            'inGlossaire' => 'Actif dans le glossaire',
            'etat' => 'Etat',
            'order' => 'Ordre d\'affichage',
            'inRecherche' => 'Présent dans la recherche',
            'codes' => 'Code(s)',
            'synonymesLibelle' => 'Synonymes',
            'champLexicalNomsLibelle' => 'Champ lexical',
            'parentLibelles' => 'Parents',
        ];

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')
            ->exportCsv($colonnes, $refs, 'export-references.csv', $kernelCharset)
        ;
    }

    /**
     * Effectue le render du formulaire Reference.
     *
     * @param $reference
     *
     * @internal param Reference $item Entité Référence
     *
     * @return RedirectResponse|Response
     */
    private function renderForm(Reference $reference)
    {
        $this->get('hopitalnumerique_reference.doctrine.reference.domaine_udpater')
            ->setInitialReference($reference)
        ;

        if ($reference->getLock()) {
            $form = $this->createForm('hopitalnumerique_reference_reference_locked', $reference);
        } else {
            $form = $this->createForm('hopitalnumerique_reference_reference', $reference);
        }

        $request = $this->get('request');
        $form->handleRequest($request);

        // Si l'utilisateur soumet le formulaire
        if ($form->isSubmitted()) {
            // get uploaded form datas (used to manipulate parent next)
            $formDatas = $request->request->get('hopitalnumerique_reference_reference');

            // On bind les données du form
            $this->container->get('hopitalnumerique_reference.doctrine.reference.domaine_udpater')
                ->updateDomaines($reference)
            ;

            // si le formulaire est valide
            if ($form->isValid()) {
                if ($reference->isAllDomaines()) {
                    $reference->removeDomaines();
                }

                if (isset($formDatas['parent']) && !is_null($formDatas['parent'])) {
                    $parent = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(
                        [
                            'id' => $formDatas['parent'],
                        ]
                    );

                    $reference->setParent($parent);

                    // Mise à jour du/des domaine(s) sur l'ensemble de l'arbre d'héritage des parents
                    $family = [];
                    $daddy = $parent;

                    // Tant qu'il y a des parents on ajoute le(s) nouveau(x) domaine(s) dessus
                    while (!is_null($daddy)) {
                        $childsDomaines = [];
                        // Vérifie si l'élément courant a un parent
                        $childs = $daddy->getChilds();

                        // Si on est au niveau du parent de la référence courante, on ajoute cette dernière au
                        // tableau des enfants du parent qui n'est pas encore setté
                        // si la référence est un ajout
                        if ($daddy->getId() === $parent->getId() && is_null($reference->getId())) {
                            if (count($childs) !== 0) {
                                foreach ($childs as $child) {
                                    $childsTemp[] = $child;
                                }

                                $childsTemp[] = $reference;
                                $childs = $childsTemp;
                            } else {
                                $childs = [$reference];
                            }
                        }

                        foreach ($childs as $child) {
                            if (count($child->getDomaines()) !== 0) {
                                foreach ($child->getDomaines() as $domaine) {
                                    if (!array_key_exists($domaine->getId(), $childsDomaines)) {
                                        $childsDomaines[$domaine->getId()] = $domaine;
                                    }
                                }
                            }
                        }

                        // Vide les domaines du père pour remettre uniquement ceux des enfants
                        // (suppression d'un domaine lors de la sauvegarde n'étant plus chez aucun enfant)
                        $daddy->setDomaines([]);

                        // Récupération des domaines du parent courant
                        // pour éviter la duplication de domaine sur une entité
                        $daddyDomainesId = $daddy->getDomainesId();
                        if (count($childsDomaines) !== 0) {
                            foreach ($childsDomaines as $domaine) {
                                if (!in_array($domaine->getId(), $daddyDomainesId)) {
                                    //Si il n'a pas encore ce domaine, on lui ajoute
                                    $daddy->addDomaine($domaine);
                                }
                            }
                        }

                        $family[] = $daddy;

                        //Parent suivant ou null si on est au sommet de l'arbre
                        $daddy = $daddy->getParent();
                    }

                    if (count($family) > 0) {
                        $this->get('hopitalnumerique_reference.manager.reference')->save($family);
                    }
                }

                //test ajout ou edition
                $new = is_null($reference->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_reference.manager.reference')->save($reference);

                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add(
                    $new ? 'success' : 'info',
                    'Reference ' . ($new ? 'ajoutée.' : 'mise à jour.')
                );

                $do = $request->request->get('do');

                return $this->redirect(
                    $do == 'save-close'
                        ? $this->generateUrl('hopitalnumerique_reference_reference')
                        : $this->generateUrl(
                            'hopitalnumerique_reference_reference_edit',
                            [
                                'id' => $reference->getId(),
                            ]
                        )
                );
            }
        }

        return $this->render('HopitalNumeriqueReferenceBundle:Reference:edit.html.twig', [
            'form' => $form->createView(),
            'reference' => $reference,
        ]);
    }

    /**
     * @param Reference $reference
     *
     * @return JsonResponse
     */
    public function getReferenceTreeAction(Reference $reference = null)
    {
        $referenceTreeOptions = [];
        $forbidden = null !== $reference ? [$reference->getId()] : [];

        if (null === $reference || !$reference->getLock()) {
            $referenceTreeOptions = $this->get('hopitalnumerique_reference.dependency_injection.reference.tree')
                ->getOptions(
                    $this->getUser()->getDomaines(),
                    $forbidden
                )
            ;
        }

        return new JsonResponse($referenceTreeOptions);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function replaceAction(Request $request)
    {
        $switchReferenceCommand = new SwitchReferenceCommand();

        $form = $this->createForm(SwitchReferenceType::class, $switchReferenceCommand, [
            'action' => $this->generateUrl('hopitalnumerique_reference_reference_replace'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->get('hopitalnumerique_reference.handler.switch_reference')->handle($switchReferenceCommand);

                $this->addFlash(
                    'success',
                    'La référence ' . $switchReferenceCommand->targetReference
                    . ' a bien été ajoutée aux objets référencés sur la référence '
                    . $switchReferenceCommand->currentReference
                );
            } catch (\Exception $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }

            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('@HopitalNumeriqueReference/Reference/replace.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
