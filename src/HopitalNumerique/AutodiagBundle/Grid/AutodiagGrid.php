<?php

namespace HopitalNumerique\AutodiagBundle\Grid;

use Nodevo\GridBundle\Grid\Action\ActionMass;
use Nodevo\GridBundle\Grid\Action\EditButton;
use Nodevo\GridBundle\Grid\Column\DateColumn;
use Nodevo\GridBundle\Grid\Column\NumberColumn;
use Nodevo\GridBundle\Grid\Column\TextColumn;
use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;

class AutodiagGrid extends Grid implements GridInterface
{
    public function setConfig()
    {
        $user = $this->_container->get('security.token_storage')->getToken()->getUser();

        $this->setSource('autodiag.repository.autodiag');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setSourceCondition('domaine', $user->getDomaines());
    }

    public function setColumns()
    {
        $this->addColonne(
            (new NumberColumn('id', 'ID'))->setVisible(false)
        );

        $this->addColonne(
            new TextColumn('title', 'Titre')
        );

        $this->addColonne(
            new TextColumn('domaines_list', 'Domaines')
        );

        $this->addColonne(
            new DateColumn('createdAt', 'Date de création')
        );

        $this->addColonne(
            new DateColumn('publicUpdatedDate', 'Date de dernière mise à jour')
        );

        $column = new NumberColumn('nb_entries_in_progress', 'Autodiag en cours');
        $column->setFilterable(false);
        $this->addColonne($column);

        $column = new NumberColumn('nb_entries_valid', 'Autodiag validés');
        $column->setFilterable(false);
        $this->addColonne($column);
    }

    public function setActionsButtons()
    {
        $this->addActionButton(
            new EditButton('hopitalnumerique_autodiag_edit')
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

                $autodiags = $this->_container->get('autodiag.repository.autodiag')->findBy([
                    'id' => $ids,
                ]);

                try {
                    foreach ($autodiags as $autodiag) {
                        $this->_container->get('doctrine.orm.entity_manager')->remove($autodiag);
                    }
                    $this->_container->get('doctrine.orm.entity_manager')->flush();
                    $this->_container->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');
                } catch (\Exception $e) {
                    $this->_container->get('session')->getFlashBag()->add('error', 'Une erreur est survenue.');
                }
            })
        );
    }
}
