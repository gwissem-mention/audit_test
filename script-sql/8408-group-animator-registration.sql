INSERT INTO hn_communautepratique_groupe_user
  SELECT
    animateur.group_id,
    animateur.usr_id,
    1
  FROM hn_communautepratique_groupe_animateur AS animateur
    LEFT JOIN hn_communautepratique_groupe_user AS user ON user.group_id = animateur.group_id AND user.usr_id = animateur.usr_id

  WHERE user.usr_id IS NULL
;


UPDATE hn_communautepratique_groupe_user AS user
  JOIN hn_communautepratique_groupe_animateur AS animateur ON user.group_id = animateur.group_id AND user.usr_id = animateur.usr_id
SET user.actif = TRUE
WHERE user.actif = FALSE;
