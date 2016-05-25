<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute;

use HopitalNumerique\AutodiagBundle\Entity\Model;

/**
 * Interface AttributeBuilderInterface
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute
 *
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
interface AttributeBuilderInterface
{
    /**
     * Get attribute type name
     *
     * @return string
     */
    public function getName();
}
