<?php

namespace HopitalNumerique\ObjetBundle;

use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Contains all events thrown in the HopitalNumerique\ObjetBundle.
 *
 * @package HopitalNumerique\ObjetBundle
 */
final class Events
{

    /**
     * L'événement objet_download_success est déclanché lors du téléchargement d'un objet.
     *
     * The event listener receives an Numerique\ObjetBundle\Entity\Objet instance.
     */
    const OBJET_DOWNLOAD_SUCCESS = 'objet_download_success';
}