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
            new ActionMass('Supprimer', 'HopitalNumeriqueAutodiagBundle:Back\Autodiag:deleteMass')
        );
    }

}
