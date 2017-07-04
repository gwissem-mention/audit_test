<?php

namespace HopitalNumerique\ObjetBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Action\EditButton;
use Nodevo\GridBundle\Grid\Column\DateColumn;
use Nodevo\GridBundle\Grid\Column\TextColumn;
use Nodevo\GridBundle\Grid\Column\ArrayColumn;
use Nodevo\GridBundle\Grid\Column\BooleanColumn;

class RiskGrid extends Grid implements GridInterface
{
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_objet.repository.risk');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setNoDataMessage('Aucun rique à afficher.');
        $this->showIDColumn(false);
        $this->setDefaultFilters(['archived' => 0]);
        $this->setDefaultOrder('createdAt', 'DESC');
    }

    public function setColumns()
    {
        $this->addColonne(new TextColumn('label', 'Titre'));

        $createdAtColumn = new DateColumn('createdAt', 'Date de création');
        $createdAtColumn->setVisible(false)->setFilterable(false);
        $this->addColonne($createdAtColumn);

        $natureColumn = new TextColumn('nature', 'Nature');
        $natureColumn
            ->setFilterType('select')
            ->setOperatorsVisible(false)
        ;
        $this->addColonne($natureColumn);

        $domainsColumn = new ArrayColumn('domains', 'Domaines');
        $domainsColumn
            ->setSeparator(', ')
            ->setFilterType('select')
            ->setOperatorsVisible(false)
        ;
        $this->addColonne($domainsColumn);

        $typeColumn = new TextColumn('type', 'Type');
        $typeColumn
            ->setFilterType('select')
            ->setOperatorsVisible(false)
            ->manipulateRenderCell(function ($value) {
                return $this->_container->get('translator')->trans(sprintf('list.type.value.%d', $value), [], 'risk');
            })
        ;
        $this->addColonne($typeColumn);

        $this->addColonne(new BooleanColumn('archived', 'Archivée'));
    }

    public function setActionsButtons()
    {
        $editBtn = new EditButton('hopitalnumerique_objet_risk_edit');
        $editBtn->setRouteParametersMapping(['id' => 'risk']);
        $this->addActionButton($editBtn);
    }

    public function setMassActions()
    {}
}
