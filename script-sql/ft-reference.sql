# !!! Avant un d:s:u --force

# Parents des références
CREATE TABLE hn_reference_has_parent (ref_parent_id INT NOT NULL COMMENT 'ID de la référence', ref_id INT NOT NULL COMMENT 'ID de la référence', INDEX IDX_4522B66B120CB35 (ref_parent_id), INDEX IDX_4522B66B21B741A9 (ref_id), PRIMARY KEY(ref_parent_id, ref_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
INSERT INTO hn_reference_has_parent(ref_parent_id, ref_id) (SELECT parent_id, ref_id FROM `hn_reference` WHERE parent_id IS NOT NULL);