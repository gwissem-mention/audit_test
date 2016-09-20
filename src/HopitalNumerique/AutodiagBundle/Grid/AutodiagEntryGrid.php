<?php
namespace HopitalNumerique\AutodiagBundle\Grid;

use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Export\SCSVExport;
use APY\DataGridBundle\Grid\Row;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\SynthesisGenerator;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\GridBundle\Grid\Action\ActionMass;
use Nodevo\GridBundle\Grid\Action\DownloadButton;
use Nodevo\GridBundle\Grid\Action\ShowButton;
use Nodevo\GridBundle\Grid\Column\BooleanColumn;
use Nodevo\GridBundle\Grid\Column\DateColumn;
use Nodevo\GridBundle\Grid\Column\NumberColumn;
use Nodevo\GridBundle\Grid\Column\TextColumn;
use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;

class AutodiagEntryGrid extends Grid implements GridInterface
{
    /**
     * @var Autodiag
     */
    protected $autodiag;

    public function setAutodiag(Autodiag $autodiag)
    {
        $this->autodiag = $autodiag;
    }

    public function setConfig()
    {
        $this->setSource('autodiag.entries.grid');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);

        $this->setSourceCondition('autodiag', $this->autodiag);
    }

    public function setColumns()
    {
        $this->addColonne(
            (new NumberColumn('id', 'ID'))->setVisible(false)
        );

        $this->addColonne(
            new TextColumn('name', 'Nom')
        );

        $this->addColonne(
            new TextColumn('user', 'Utilisateur')
        );

        $this->addColonne(
            new TextColumn('etablissement', 'Établissement')
        );

//        $this->addColonne(
//            new TextColumn('remplissage', 'Remplissage')
//        );

        $this->addColonne(
            new DateColumn('created_at', 'Création')
        );

        $this->addColonne(
            new DateColumn('updated_at', 'Dernier enregistrement')
        );

        $this->addColonne(
            new DateColumn('validated_at', 'Validation')
        );

        $this->addColonne(
            new TextColumn('shares', 'Partagé avec')
        );

        $this->addColonne(
            new BooleanColumn('is_synthesis', 'Synthèse')
        );
    }

    public function setActionsButtons()
    {
        $downloadAction = new DownloadButton('hopitalnumerique_autodiag_restitution_pdf');
        $downloadAction->setRouteParametersMapping(['id' => 'synthesis']);
        $downloadAction->setAttributes(array_merge($downloadAction->getAttributes(), ['target' => '_blank']));

        $this->addActionButton(
            $downloadAction
        );


        $showAction = new ShowButton('hopitalnumerique_autodiag_edit_result_show_entry');

        $showAction->manipulateRender(function (RowAction $action, Row $row) {
            if (null === $row->getField('entry_id') || strlen($row->getField('entry_id')) === 0) {
                return null;
            }

            $action->setRouteParameters(['entry_id']);
            $action->setRouteParametersMapping(['entry_id' => 'entry']);

            return $action;
        });

        $this->addActionButton(
            $showAction
        );
    }

    public function setMassActions()
    {
        $this->addMassAction(
            new ActionMass('Supprimer', function ($ids, $all) {
                //get all selected Codes
                if ($all == 1) {
                    $rawDatas = $this->getRawData();
                    foreach ($rawDatas as $data) {
                        $ids[] = $data['id'];
                    }
                }

                $syntheses = $this->_container->get('autodiag.repository.synthesis')->findBy([
                    'id' => $ids
                ]);

                try {
                    $user = $this->_container->get('security.token_storage')->getToken()->getUser();
                    if ($user instanceof User) {
                        foreach ($syntheses as $synthesis) {
                            $this->_container->get('autodiag.synthesis.remover')->removeSynthesis($synthesis, $user);
                        }
                        $this->_container->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');
                    } else {
                        throw new \Exception('User not allowed');
                    }

                } catch (\Exception $e) {
                    $this->_container->get('session')->getFlashBag()->add('danger', "Une erreur est survenue.");
                }
            })
        );

        if ($this->autodiag->isSynthesisAuthorized()) {
            $this->addMassAction(
                new ActionMass(
                    'Créer une synthèse',
                    function ($ids, $all) {
                        //get all selected Codes
                        if ($all == 1) {
                            $rawDatas = $this->getRawData();
                            foreach ($rawDatas as $data) {
                                $ids[] = $data['id'];
                            }
                        }

                        $syntheses = $this->_container->get('autodiag.repository.synthesis')->findBy([
                            'id' => $ids
                        ]);

                        try {
                            $newSynthesis = $this->_container->get('autodiag.synthesis.generator')->generateSynthesis(
                                $this->autodiag,
                                $syntheses,
                                $this->_container->get('security.token_storage')->getToken()->getUser()
                            );
                            $this->_container->get('doctrine.orm.entity_manager')->persist($newSynthesis);
                            $this->_container->get('session')->getFlashBag()->add('info', 'La synthèse à bien été créée.');
                        } catch (\Exception $e) {
                            if ($e->getCode() == SynthesisGenerator::NEED_AT_LEAST_2) {
                                $this->_container->get('session')->getFlashBag()->add(
                                    'danger',
                                    "Vous devez sélectionner au moins deux éléments."
                                );
                            } elseif ($e->getCode() == SynthesisGenerator::SYNTHESIS_NOT_VALIDATED) {
                                $this->_container->get('session')->getFlashBag()->add(
                                    'danger',
                                    "Tous les autodiagnostics doivent etre validés avant de pouvoir créer une synthèse."
                                );
                            } else {
                                $this->_container->get('session')->getFlashBag()->add('danger', "Une erreur est survenue.");
                            }
                        }

                        $this->_container->get('doctrine.orm.entity_manager')->flush();

                    }
                )
            );
        }

        $this->addMassAction(
            new ActionMass('Export Excel des résultats', function ($ids, $all) {
                //get all selected Codes
                if ($all == 1) {
                    $rawDatas = $this->getRawData();
                    foreach ($rawDatas as $data) {
                        $ids[] = $data['id'];
                    }
                }

                $syntheses = $this->_container->get('autodiag.repository.synthesis')->findBy([
                    'id' => $ids
                ]);

                if (!is_array($syntheses) || count($syntheses) === 0) {
                    $this->_container->get('session')->getFlashBag()->add('danger', "Veuillez séléctionner au moins une ligne.");
                    return false;
                }

                try {

                    return $this->_container->get('autodiag.entries.export')->exportList($syntheses);

                    $this->_container->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');
                } catch (\Exception $e) {
                    $this->_container->get('session')->getFlashBag()->add('danger', "Une erreur est survenue.");
                }
            })
        );

        $this->_grid->addExport(
            new SCSVExport('Export du tableau')
        );
    }

    public function getDatasForGrid($autodiag)
    {
        $qb = $this->_container->get('autodiag.repository.synthesis')->getDatasForGrid($autodiag->value);
        $resultSet = $qb->getQuery()->getResult();

        $data = [];
        foreach ($resultSet as $synthesis) {
            /** @var Synthesis $synthesis */

            $shares = [];
            if ($synthesis->getShares()->count() > 0) {
                $shares = $synthesis->getShares()->map(function (User $user) {
                    return sprintf('%s %s', $user->getPrenom(), $user->getNom());
                })->toArray();
            }

            $entryId = $synthesis->getEntries()->count() > 0 && $synthesis->getEntries()->count() < 2
                ? $synthesis->getEntries()->first()->getId()
                : null;

            $data[] = [
                'id' => $synthesis->getId(),
                'name' => $synthesis->getName(),
                'user' => $synthesis->getUser() !== null
                    ? sprintf('%s %s', $synthesis->getUser()->getPrenom(), $synthesis->getUser()->getNom())
                    : 'Anonyme',
                'etablissement' => null !== $synthesis->getUser() && null !== $synthesis->getUser()->getEtablissementRattachementSante()
                    ? $synthesis->getUser()->getEtablissementRattachementSante()->getNom()
                    : '',
//                'remplissage' => sprintf('%d%%', $this->_container->get('autodiag.synthesis.completion')->getCompletionRate($synthesis)),
                'created_at' => $synthesis->getCreatedAt(),
                'updated_at' => $synthesis->getUpdatedAt(),
                'validated_at' => $synthesis->getValidatedAt(),
                'shares' => implode(", ", $shares),
                'is_synthesis' => $synthesis->getEntries()->count() > 1,
                'entry_id' => $entryId,
            ];
        }

        return $data;
    }
}
