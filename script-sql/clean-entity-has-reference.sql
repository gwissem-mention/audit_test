/* Forum topic */
DELETE FROM hn_entity_has_reference WHERE entref_entity_type = 3 AND (SELECT id FROM hn_forum_topic WHERE id = entref_entity_id) IS NULL
