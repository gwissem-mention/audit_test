<?php
namespace Nodevo\GridBundle\Grid;

/**
 * Interface Grid
 */
interface IGrid
{
    public function setConfig();
    public function setColumns();
    public function setActionsButtons();
    public function setMassActions();
} 