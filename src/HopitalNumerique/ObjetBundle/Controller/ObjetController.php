<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Gedmo\Loggable\Entity\LogEntry;
use HopitalNumerique\CoreBundle\Service\ObjectIdentity\LinkGenerator;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Model\Report;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\RelatedBoard;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\ObjetBundle\Repository\ObjectUpdateRepository;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\ObjetBundle\Domain\Command\AddObjectUpdateHandler;
use HopitalNumerique\ObjetBundle\Domain\Command\AddObjectUpdateCommand;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;

/**
 * Objet controller.
 */
class ObjetController extends Controller
{
    /**
     * Affiche la liste des Objet.
     *
     * @return Response
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_objet.grid.objet');

        return $grid->render('HopitalNumeriqueObjetBundle:Objet:index.html.twig');
    }

    /**
     * Affiche la liste des Objet sous fomes d'arbre.
     *
     * @return Response
     */
    public function treeIndexAction()
    {
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjets();

        return $this->render('HopitalNumeriqueObjetBundle:Index:index.html.twig', [
            'objets' => $objets,
        ]);
    }

    /**
     * Affiche la liste des Objet.
     *
     * @param string $filtre
     * @param string|null $domain
     *
     * @return Response
     */
    public function indexFiltreAction($filtre, $domain = null)
    {
        $grid = $this->get('hopitalnumerique_objet.grid.objet');
        $grid->setId($filtre);

        if (!is_null($filtre)) {
            $grid->setDefaultFiltreFromController($filtre, $domain);
        }

        return $grid->render('HopitalNumeriqueObjetBundle:Objet:index.html.twig', [
            'filtre' => $filtre,
        ]);
    }

    /**
     * Action Annuler, on dévérouille l'objet et on redirige vers l'index.
     *
     * @param $id
     * @param $message
     * @param $filtre
     *
     * @return RedirectResponse
     */
    public function cancelWithFiltreAction($id, $message, $filtre)
    {
        /** @var Objet $objet */
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy([
            'id' => $id,
        ]);

        $this->get('hopitalnumerique_objet.manager.objet')->unlock($objet);

        // si on à appellé l'action depuis le button du grid, on met un message à l'user, sinon pas besoin de message
        if (!is_null($message)) {
            $this->addFlash('info', 'Objet dévérouillé.');
        }

        return $this->redirect($this->generateUrl('hopitalnumerique_objet_objet_filtre', [
            'filtre' => $filtre,
        ]));
    }

    /**
     * Action Annuler, on dévérouille l'objet et on redirige vers l'index.
     *
     * @param $id
     * @param $message
     *
     * @return RedirectResponse
     */
    public function cancelAction($id, $message)
    {
        /** @var Objet $objet */
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(['id' => $id]);

        $this->get('hopitalnumerique_objet.manager.objet')->unlock($objet);

        //si on à appellé l'action depuis le button du grid, on met un message à l'user, sinon pas besoin de message
        if (!is_null($message)) {
            $this->addFlash('info', 'Objet dévérouillé.');
        }

        return $this->redirect($this->generateUrl('hopitalnumerique_objet_objet'));
    }

    /**
     * Affiche le formulaire d'ajout de Objet.
     *
     * @param $type
     *
     * @return RedirectResponse|Response
     */
    public function addAction($type)
    {
        $objet = new Objet();

        if ($type == 2) {
            $objet->setArticle(true);
        }

        $options = [
            'toRef' => 0,
            'note' => 0,
        ];

        return $this->renderForm(
            'hopitalnumerique_objet_objet',
            $objet,
            'HopitalNumeriqueObjetBundle:Objet:edit.html.twig',
            $options
        );
    }

    /**
     * Affiche le formulaire d'édition de Objet.
     *
     * @param $id
     * @param $infra
     * @param $toRef
     *
     * @return RedirectResponse|Response
     */
    public function editAction($id, $infra, $toRef)
    {
        /** @var Objet $objet */
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(['id' => $id]);

        if (is_null($objet)) {
            $this->addFlash('info', 'L\'objet n\'existe pas.');

            return $this->redirect($this->generateUrl('hopitalnumerique_objet_objet'));
        }

        $this->get(LinkGenerator::class)->getLink(ObjectIdentity::createFromDomainObject($objet), 'admin_edit');

        $user = $this->getUser();

        // l'objet est locked, on redirige vers la home page
        if ($objet->getLock() && $objet->getLockedBy() && $objet->getLockedBy() != $user) {
            $this->addFlash(
                'warning',
                'Cet objet est en cours d\'édition par '
                . $objet->getLockedBy()->getEmail()
                . ', il n\'est donc pas accessible pour le moment.'
            );

            return $this->redirect($this->generateUrl('hopitalnumerique_objet_objet'));
        }

        $objet = $this->get('hopitalnumerique_objet.manager.objet')->lock($objet, $user);
        //get Contenus
        $this->get('hopitalnumerique_objet.manager.contenu')->setRefPonderees(
            $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees($objet->getDomainesId())
        );
        $contenus = $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet($id, $objet->getDomainesId());

        $options = [
            'contenus' => $contenus,
            'infra' => $infra,
            'toRef' => $toRef,
            'objectsRelated' => $this->get(ObjectIdentityRepository::class)->getRelatedObjects(ObjectIdentity::createFromDomainObject($objet)),
            'relatedObjects' => $this->get(ObjectIdentityRepository::class)->getRelatedByObjects(ObjectIdentity::createFromDomainObject($objet)),
            'domainesCommunsWithUser' => $this
                ->get('hopitalnumerique_core.dependency_injection.entity')
                ->getEntityDomainesCommunsWithUser($objet, $user)
            ,
        ];

        return $this->renderForm(
            'hopitalnumerique_objet_objet',
            $objet,
            'HopitalNumeriqueObjetBundle:Objet:edit.html.twig',
            $options
        );
    }

    /**
     * Affiche le Objet en fonction de son ID passé en paramètre.
     *
     * @param $id
     *
     * @return Response
     */
    public function showAction($id)
    {
        /** @var Objet $objet */
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(['id' => $id]);

        //get History
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $logs = $repo->getLogEntries($objet);

        usort($logs, function (LogEntry $log1, LogEntry $log2) {
            return $log1->getLoggedAt() < $log2->getLoggedAt();
        });

        $updates = $this->get(ObjectUpdateRepository::class)->findBy(['object' => $objet], ['updatedAt' => 'DESC']);

        return $this->render('HopitalNumeriqueObjetBundle:Objet:show.html.twig', [
            'objet' => $objet,
            'logs' => $logs,
            'updates' => $updates,
        ]);
    }

    /**
     * Suppresion d'un Objet.
     * METHOD = POST|DELETE.
     *
     * @param $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        /** @var Objet $objet */
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(['id' => $id]);

        if ($objet->isArticle()) {
            $filtre = 'Article';
        } else {
            $filtre = 'publication';
        }

        //Suppression de l'entitée
        $this->get('hopitalnumerique_objet.manager.objet')->delete($objet);

        $this->addFlash('info', 'Suppression effectuée avec succès.');

        return new Response(
            '{"success":true, "url" : "' . $this->generateUrl(
                'hopitalnumerique_objet_objet_filtre',
                ['filtre' => $filtre]
            ) . '"}',
            200
        );
    }

    /**
     * Suppression de masse des objets.
     *
     * @param array $primaryKeys    Id des lignes sélectionnées
     * @param array $allPrimaryKeys
     *
     * @return RedirectResponse
     */
    public function deleteMassAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_objet.manager.objet')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $objets = $this->get('hopitalnumerique_objet.manager.objet')->findBy(['id' => $primaryKeys]);

        try {
            //Suppression de l'etablissement
            $this->get('hopitalnumerique_objet.manager.objet')->delete($objets);
            $this->addFlash('info', 'Suppression effectuée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash(
                'danger',
                'Suppression impossible, l\'objet est actuellement lié et ne peut pas être supprimé.'
            );
        }

        return $this->redirect($this->generateUrl('hopitalnumerique_objet_objet'));
    }

    /**
     * Vérifie l'unicité du nom du fichier.
     *
     * @return Response
     */
    public function isFileExistAction()
    {
        //get uploaded file name and parse it
        $fileName = $this->get('request')->request->get('fileName');
        $fileName = explode('\\', $fileName);

        //seek if the file already exist for objets
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(['path' => end($fileName)]);
        $result = is_null($objet) ? 'false' : 'true';

        //return success.true si le fichier existe deja
        return new Response('{"success":' . $result . '}', 200);
    }

    /**
     * Action appelée dans le plugin "Publication" pour tinymce.
     */
    public function getObjetsAction()
    {
        $arbo = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsAndContenuArbo();

        return $this->render('HopitalNumeriqueObjetBundle:Objet:getObjets.html.twig', [
            'objet' => $arbo,
            'texte' => $this->get('request')->request->get('texte'),
        ]);
    }

    /**
     * Action appelée dans le plugin "Publication" pour tinymce.
     */
    public function getObjetsByDomaineAction()
    {
        $arbo = $this->get('hopitalnumerique_objet.manager.objet')->getObjetByDomaine();

        return $this->render('HopitalNumeriqueObjetBundle:Objet:getObjets.html.twig', [
            'objet' => $arbo,
            'texte' => $this->get('request')->request->get('texte'),
        ]);
    }

    /**
     * POPIN : liste des publication (utilisé dans le menu item).
     *
     * @param $articles
     *
     * @return Response
     */
    public function getPublicationsAction($articles)
    {
        if ($articles == 1) {
            $types = $this->get('hopitalnumerique_reference.manager.reference')->findByCode('CATEGORIE_OBJET');
            $arbo = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsAndContenuArbo($types);
        } else {
            $types = $this->get('hopitalnumerique_reference.manager.reference')->findByCode('CATEGORIE_ARTICLE');
            $arbo = $this->get('hopitalnumerique_objet.manager.objet')->getArticlesArbo($types);
        }

        return $this->render('HopitalNumeriqueObjetBundle:Objet:getPublications.html.twig', [
            'objets' => $arbo,
        ]);
    }

    /**
     * Génère les données requises pour les paramètres de l'url (type publication).
     */
    public function getPublicationDetailsForMenuAction()
    {
        $publication = explode(':', $this->get('request')->request->get('publication'));
        $result = ['success' => true];

        if (isset($publication[0]) && isset($publication[1])) {
            if ($publication[0] === 'PUBLICATION') {
                /** @var Objet $objet */
                $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(['id' => $publication[1]]);

                //set URL to select
                $result['url'] = 'hopital_numerique_publication_publication_objet';

                //set params for URL
                $result['id'] = $objet->getId();
                $result['alias'] = $objet->getAlias();
            } elseif ($publication[0] === 'INFRADOC') {
                /** @var Contenu $contenu */
                $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(['id' => $publication[1]]);

                //set URL to select
                $result['url'] = 'hopital_numerique_publication_publication_contenu';

                //set params for URL
                $result['id'] = $contenu->getObjet()->getId();
                $result['alias'] = $contenu->getObjet()->getAlias();
                $result['idc'] = $contenu->getId();
                $result['aliasc'] = $contenu->getAlias();
            } elseif ($publication[0] === 'ARTICLE') {
                /** @var Objet $objet */
                $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(['id' => $publication[1]]);
                $types = $objet->getTypes();
                $type = $types[0];
                $categorie = '';

                /** @var Reference $parent */
                if ($parent = $type->getParent()) {
                    $categorie .= $parent->getLibelle() . '-';
                }
                $categorie .= $type->getLibelle();

                //clean categ
                $tool = new Chaine($categorie);

                //set URL to select
                $result['url'] = 'hopital_numerique_publication_publication_article';

                //set params for URL
                $result['id'] = $objet->getId();
                $result['alias'] = $objet->getAlias();
                $result['categorie'] = $tool->minifie();
            } else {
                $result['success'] = false;
            }
        } else {
            $result['success'] = false;
        }

        return new Response(json_encode($result), 200);
    }

    /**
     * Generate the article feed (RSS).
     *
     * @param Request $request
     *
     * @return Response XML Feed
     */
    public function feedAction(Request $request)
    {
        /** @var Domaine $domaine */
        $domaine = $this->get('hopitalnumerique_domaine.manager.domaine')->findOneById(
            $request->getSession()->get('domaineId', 1)
        );

        $actualites = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsForRSS($domaine);

        $feed = $this->get('eko_feed.feed.manager')->get('objet');
        $feed->addFromArray($actualites);

        return new Response($feed->render('rss'));
    }

    /**
     * Effectue le render du formulaire Objet.
     *
     * @param       $formName
     * @param Objet $objet
     * @param       $view
     * @param array $options
     *
     * @return RedirectResponse|Response
     */
    private function renderForm($formName, $objet, $view, $options = [])
    {
        //Création du formulaire via le service
        $form = $this->createForm($formName, $objet);

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);

            //Vérification de la présence rôle et des types
            $formTypes = $form->get('types')->getData();

            if (is_null($formTypes)) {
                $this->addFlash('danger', 'Veuillez sélectionner un type d\'objet.');

                return $this->render($view, [
                    'form' => $form->createView(),
                    'objet' => $objet,
                    'contenus' => isset($options['contenus']) ? $options['contenus'] : [],
                    'infra' => isset($options['infra']) ? $options['infra'] : false,
                    'toRef' => isset($options['toRef']) ? $options['toRef'] : false,
                    'note' => isset($options['note']) ? $options['note'] : 0,
                    'objectsRelated' => isset($options['objectsRelated']) ? $options['objectsRelated'] : [],
                    'relatedObjects' => isset($options['relatedObjects']) ? $options['relatedObjects'] : [],
                    'domainesCommunsWithUser' => isset($options['domainesCommunsWithUser'])
                        ? $options['domainesCommunsWithUser'] : [],
                ]);
            }

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($objet->getId());

                //si l'alias est vide, on le génère depuis le titre
                $tool = new Chaine(($objet->getAlias() == '' ? $objet->getTitre() : $objet->getAlias()));
                $objet->setAlias($tool->minifie());

                //Test if alias already exist
                if ($this->get('hopitalnumerique_objet.manager.objet')->testAliasExist($objet, $new)) {
                    $this->addFlash('danger', 'Cet Alias existe déjà.');

                    return $this->render($view, [
                        'form' => $form->createView(),
                        'objet' => $objet,
                        'contenus' => isset($options['contenus']) ? $options['contenus'] : [],
                        'infra' => isset($options['infra']) ? $options['infra'] : false,
                        'toRef' => isset($options['toRef']) ? $options['toRef'] : false,
                        'note' => isset($options['note']) ? $options['note'] : 0,
                        'objectsRelated' => isset($options['objectsRelated']) ? $options['objectsRelated'] : [],
                        'relatedObjects' => isset($options['relatedObjects']) ? $options['relatedObjects'] : [],
                        'domainesCommunsWithUser' => isset($options['domainesCommunsWithUser'])
                            ? $options['domainesCommunsWithUser'] : [],
                    ]);
                }

                //Object security isArticle = false
                if (is_null($objet->isArticle())) {
                    $objet->setArticle(false);
                }

                //Met à jour la date de modification
                $notify = $form->get('modified')->getData();
                if ($notify === '1') {
                    $reason = $form->get('reason')->getData();

                    $addObjectUpdateCommand = new AddObjectUpdateCommand(
                        $objet,
                        $this->getUser(),
                        $reason
                    );

                    $this->get(AddObjectUpdateHandler::class)->handle($addObjectUpdateCommand);

                    $objet->setDateModification(new \DateTime());
                }

                //si on à choisis fermer et sauvegarder : on unlock l'user (unlock + save)
                $do = $request->request->get('do');
                $this->get('hopitalnumerique_objet.manager.objet')->unlock($objet);

                $this->get('hopitalnumerique_reference.doctrine.glossaire.parse')->parseAndSaveEntity($objet);

                // On envoie une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                if ($do == 'save-auto') {
                    $this->addFlash('info', 'Objet sauvegardé automatiquement.');
                } else {
                    $this->addFlash(
                        ($new ? 'success' : 'info'),
                        'Objet ' . ($new ? 'ajouté.' : 'mis à jour.')
                    );
                }

                // On redirige vers la home page
                if ($objet->isArticle()) {
                    return $this->redirect(
                        ($do == 'save-close'
                            ? $this->generateUrl('hopitalnumerique_objet_objet_filtre', ['filtre' => 'Article'])
                            : $this->generateUrl(
                                'hopitalnumerique_objet_objet_edit',
                                [
                                    'id' => $objet->getId(),
                                ]
                            ))
                    );
                }

                return $this->redirect(
                    ($do == 'save-close'
                        ? $this->generateUrl('hopitalnumerique_objet_objet_filtre', ['filtre' => 'publication'])
                        : $this->generateUrl(
                            'hopitalnumerique_objet_objet_edit',
                            [
                                'id' => $objet->getId(),
                            ]
                        ))
                );
            } else {
                $this->addFlash('danger', 'Une erreur est survenue pendant la mise à jour de l\'objet');
            }
        }

        return $this->render(
            $view,
            [
                'form' => $form->createView(),
                'objet' => $objet,
                'contenus' => isset($options['contenus']) ? $options['contenus'] : [],
                'infra' => isset($options['infra']) ? $options['infra'] : false,
                'toRef' => isset($options['toRef']) ? $options['toRef'] : false,
                'note' => isset($options['note']) ? $options['note'] : 0,
                'objectsRelated' => isset($options['objectsRelated']) ? $options['objectsRelated'] : [],
                'relatedObjects' => isset($options['relatedObjects']) ? $options['relatedObjects'] : [],
                'domainesCommunsWithUser' => isset($options['domainesCommunsWithUser'])
                    ? $options['domainesCommunsWithUser'] : [],
            ]
        );
    }

    /**
     * @param Objet $object
     *
     * @return Response
     */
    public function reportPopinAction(Objet $object)
    {
        $report = new Report();
        $report = $report->buildReport($object);

        return $this->render('@HopitalNumeriqueObjet/Objet/report.html.twig', [
            'report' => $report,
        ]);
    }

    /**
     * @param $primaryKeys
     * @param $allPrimaryKeys
     *
     * @return Response
     */
    public function exportReportAction($primaryKeys, $allPrimaryKeys)
    {
        $kernelCharset = $this->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_objet.service.report_export')->export(
            $primaryKeys,
            $allPrimaryKeys,
            $kernelCharset
        );
    }
}
