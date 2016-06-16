<?php
namespace HopitalNumerique\AutodiagBundle\Grid;

use Nodevo\GridBundle\Grid\Column\NumberColumn;
use Nodevo\GridBundle\Grid\Column\TextColumn;
use Nodevo\GridBundle\Grid\Action\ShowButton;
use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;

class ModelGrid extends Grid implements GridInterface
{
    public function setConfig()
    {
        $this->setSource('\HopitalNumerique\AutodiagBundle\Entity\Autodiag');
        $this->setSourceType(self::SOURCE_TYPE_ENTITY);
    }

    public function setColumns()
    {
        $this->addColonne(
            new NumberColumn('id', 'ID')
        );

        $this->addColonne(
            new TextColumn('title', 'Code')
        );
    }

    public function setActionsButtons()
    {
        $this->addActionButton(
            new ShowButton('hopitalnumerique_autodiag_edit')
        );
    }

    public function setMassActions()
    {

    }

}
