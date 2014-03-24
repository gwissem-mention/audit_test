<?php
namespace Nodevo\ToolsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Javascript extends Constraint
{
    public $class = '';
    public $mask  = '';
}
