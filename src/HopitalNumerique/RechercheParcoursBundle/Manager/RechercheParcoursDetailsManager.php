<?php

namespace HopitalNumerique\RechercheParcoursBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class RechercheParcoursDetailsManager extends BaseManager
{
    protected $class = 'HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails';

    public function countDetails()
    {
        return $this->getRepository()->countDetails()->getQuery()->getSingleScalarResult();
    }

    /**
     * Met à jour l'ordre du détails.
     *
     * @param array  $elements Les éléments
     * @param object $parent   L'élément parent | null
     *
     * @return empty
     */
    public function reorder($elements)
    {
        $order = 1;

        foreach ($elements as $element) {
            $rechercheParcoursDetails = $this->findOneBy(['id' => $element['id']]);
            $rechercheParcoursDetails->setOrder($order);
            ++$order;
        }
    }
}
