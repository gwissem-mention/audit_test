<?php

namespace HopitalNumerique\ObjetBundle;

/**
 * Contains all events thrown in the HopitalNumerique\ObjetBundle.
 */
final class Events
{
    /**
     * L'événement objet_download_success est déclenché lors du téléchargement d'un objet.
     *
     * The event listener receives an Numerique\ObjetBundle\Entity\Objet instance.
     */
    const OBJET_DOWNLOAD_SUCCESS = 'objet_download_success';

    /**
     * L'évènement object_noted est déclenché lors de la notation d'un objet
     *
     * The event listener receives an HopitalNumerique\ObjetBundle\Entity\Note instance
     */
    const OBJECT_NOTED = 'object_noted';
}
