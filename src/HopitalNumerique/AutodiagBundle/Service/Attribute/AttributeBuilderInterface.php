<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

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

    /**
     * Get form type class name
     *
     * @return mixed
     */
    public function getFormType();

    /**
     * Get attribute score
     *
     * @param $data
     * @return float
     */
    public function computeScore($data);

    /**
     * Check if attribute data is considered as empty
     *
     * @param $data
     * @return mixed
     */
    public function isEmpty($data);
}
