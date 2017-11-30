<?php

namespace HopitalNumerique\CommunautePratiqueBundle;

/**
 * Contain all events thrown in the autodiag bundle.
 */
final class Events
{
    /**
     * This event occurs when a user join the Communaute de pratique
     */
    const ENROLL_USER = 'communautepratique.enroll_user';

    /**
     * This event occurs when a user quit the Communaute de pratique
     */
    const DISENROLL_USER = 'communautepratique.disenroll_user';

    /**
     * This event occurs when a comment is left on a group.
     */
    const GROUP_COMMENT_CREATED = 'communautepratique.group_comment_created';

    /**
     * This event occurs when a comment is left on a form in a group.
     */
    const FORM_COMMENT_CREATED = 'communautepratique.form_comment_created';

    /**
     * This event occurs when a document is uploaded in a group.
     */
    const GROUP_DOCUMENT_CREATED = 'communautepratique.group_document_created';

    /**
     * This event occurs when a new user has joined a group.
     */
    const GROUP_USER_JOINED = 'communautepratique.group_user_joined';

    /**
     * This event occurs when a new group has been created.
     */
    const GROUP_CREATED = 'communautepratique.group_created';
}
