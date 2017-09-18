<?php

namespace HopitalNumerique\CartBundle\Service;

use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;

/**
 * Class GetCartableType.
 */
class GetCartableType
{
    const ENTITY_TYPE_TO_CART_ITEM_TYPE_MAP = [
        Entity::ENTITY_TYPE_AMBASSADEUR => Item::PERSON_TYPE,
        Entity::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE => Item::CDP_GROUP_TYPE,
        Entity::ENTITY_TYPE_CONTENU => Item::CONTENT_TYPE,
        Entity::ENTITY_TYPE_FORUM_TOPIC => Item::FORUM_TOPIC_TYPE,
        Entity::ENTITY_TYPE_OBJET => Item::OBJECT_TYPE,
        Entity::ENTITY_TYPE_RECHERCHE_PARCOURS => Item::GUIDED_SEARCH_TYPE,
    ];

    /**
     * Returns a cart item type (code) that matches an entity type (id).
     *
     * @param $entityTypeId
     *
     * @return string Cart item type code or null if no match.
     */
    public function getCartableType($entityTypeId)
    {
        if (array_key_exists($entityTypeId, self::ENTITY_TYPE_TO_CART_ITEM_TYPE_MAP)) {
            return self::ENTITY_TYPE_TO_CART_ITEM_TYPE_MAP[$entityTypeId];
        }

        return null;
    }
}