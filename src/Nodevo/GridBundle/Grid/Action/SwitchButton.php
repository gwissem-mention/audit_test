<?php
namespace Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;

class SwitchButton extends RowAction
{
    public function __construct($route)
    {
        parent::__construct('', $route);

        $this->setRouteParameters( array('id') );
        $this->setAttributes( array('class'=>'btn btn-primary fa fa-external-link','title' => 'Devenir l\'utilisateur') );
    }
}
