<?php
namespace Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;

class DownloadButton extends RowAction
{
    public function __construct($route)
    {
        parent::__construct('', $route);

        $this->setRouteParameters( array('id') );
        $this->setAttributes( array('class'=>'btn btn-primary fa fa-download','title' => 'Télécharger') );
    }
}