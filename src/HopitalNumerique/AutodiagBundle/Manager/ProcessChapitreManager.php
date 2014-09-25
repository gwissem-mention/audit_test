<?php
/**
 * Manager de ProcessChapitre.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\AutodiagBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;


/**
 * Manager de ProcessChapitre.
 */
class ProcessChapitreManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\ProcessChapitre';
}
