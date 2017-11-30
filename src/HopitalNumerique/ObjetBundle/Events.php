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
     * This event occurs when a publication or a publication part is updated.
     */
    const PUBLICATION_NOTIFIED = 'publication_notified';

    /**
     * This event occurs when a publication or a publication part is commented.
     */
    const PUBLICATION_COMMENTED = 'publication_commented';

    /**
     * L'évènement object_noted est déclenché lors de la notation d'un objet
     *
     * The event listener receives an HopitalNumerique\ObjetBundle\Entity\Note instance
     */
    const OBJECT_NOTED = 'object_noted';
}
