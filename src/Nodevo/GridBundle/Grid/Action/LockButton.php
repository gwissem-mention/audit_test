<?php
namespace Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;

class LockButton extends RowAction
{
    public function __construct($route)
    {
        parent::__construct('', $route);

        $this->setRouteParameters( array('id') );

        $this->manipulateRender(function($action, $row) {
            if( $row->getField( 'lock') )
                $action->setAttributes( array('class'=>'btn btn-warning fa fa-lock', 'title' => 'Vérouiller') );
            else
                $action->setAttributes( array('class'=>'btn btn-warning fa fa-unlock', 'title' => 'Dévérouiller') );

            return $action;
        });
    }
}