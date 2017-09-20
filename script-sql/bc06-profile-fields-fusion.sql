UPDATE core_user SET usr_presentation = CONCAT(usr_presentation, CHAR(13), CHAR(10), CHAR(13), CHAR(10), usr_biographie) WHERE usr_biographie IS NOT NULL AND usr_presentation IS NOT NULL;
UPDATE core_user SET usr_presentation = usr_biographie WHERE usr_biographie IS NOT NULL AND usr_presentation IS NULL;
ALTER TABLE core_user DROP usr_biographie;
