<?php
/**
 * Manager de Process.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\AutodiagBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de Process.
 */
class ProcessManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Process';
}
