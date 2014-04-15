<?php
namespace Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;

class FilsButton extends RowAction
{
    public function __construct($route)
    {
        parent::__construct('', $route);

        $this->setRouteParameters( array('id') );
        $this->setAttributes( array('class'=>'btn btn-success fa fa-list','title' => 'Afficher les enfants') );
    }
}