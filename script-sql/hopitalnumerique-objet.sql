/* Pas besoin de jointure, suffit d'une requête imbriquée */
DELETE FROM `hn_objet_consultation` WHERE `obj_id` IN (SELECT `obj_id` FROM `hn_objet` WHERE `obj_isArticle` = true );
