INSERT INTO `hn_intervention_forfait_transport` (`intft_id`, `intft_distance_maximum`, `intft_cout`)
VALUES (NULL, '50', '15'), (NULL, '150', '40'), (NULL, '250', '70'), (NULL, '400', '100'), (NULL, '600', '150'), (NULL, '65000', '300');

UPDATE `hn_facture_remboursement` SET `rem_supplement` = 320 WHERE `hn_facture_remboursement`.`rem_id` = 2;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = 320 WHERE `hn_facture_remboursement`.`rem_id` = 3;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = 170 WHERE `hn_facture_remboursement`.`rem_id` = 6;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = 170 WHERE `hn_facture_remboursement`.`rem_id` = 7;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = 320 WHERE `hn_facture_remboursement`.`rem_id` = 8;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = NULL WHERE `hn_facture_remboursement`.`rem_id` = 9;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = 170 WHERE `hn_facture_remboursement`.`rem_id` = 10;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = 15 WHERE `hn_facture_remboursement`.`rem_id` = 14;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = NULL WHERE `hn_facture_remboursement`.`rem_id` = 15;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = NULL WHERE `hn_facture_remboursement`.`rem_id` = 16;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = NULL WHERE `hn_facture_remboursement`.`rem_id` = 17;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = NULL WHERE `hn_facture_remboursement`.`rem_id` = 18;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = 320 WHERE `hn_facture_remboursement`.`rem_id` = 19;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = 320 WHERE `hn_facture_remboursement`.`rem_id` = 20;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = 320 WHERE `hn_facture_remboursement`.`rem_id` = 22;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = 320 WHERE `hn_facture_remboursement`.`rem_id` = 25;
UPDATE `hn_facture_remboursement` SET `rem_supplement` = 320 WHERE `hn_facture_remboursement`.`rem_id` = 26;
