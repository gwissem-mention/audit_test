<?php

namespace HopitalNumerique\AutodiagBundle\Service\Attribute;

/**
 * Interface AttributeBuilderInterface.
 *
 *
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
interface AttributeBuilderInterface
{
    /**
     * Get attribute type name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get twig template base name.
     *
     * @return string
     */
    public function getTemplateName();

    /**
     * Get form type class name.
     *
     * @return mixed
     */
    public function getFormType();

    /**
     * Get attribute score.
     *
     * @param $data
     *
     * @return float
     */
    public function computeScore($data);

    /**
     * Check if attribute data is considered as empty.
     *
     * @param $data
     *
     * @return mixed
     */
    public function isEmpty($data);

    public function transform($data);

    public function reverseTransform($data);
}
