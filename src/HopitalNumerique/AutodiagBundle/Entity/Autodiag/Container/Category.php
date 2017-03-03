<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;

/**
 * Category.
 *
 * @ORM\Table(name="ad_autodiag_container_category")
 * @ORM\Entity
 */
class Category extends Container
{
    public function getExtendedLabel()
    {
        return $this->getLabel();
    }
}
