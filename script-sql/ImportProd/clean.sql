#Users
UPDATE `core_user` SET `usr_email` = CONCAT(`usr_email`, '_test');
UPDATE `core_user` SET `usr_email_canonical` = `usr_email`;

#Domaines
UPDATE `hn_domaine` SET `dom_adresse_mail_contact` = CONCAT(`dom_adresse_mail_contact`, '_test');

#Optionnal : Set xspevu as password to all users
UPDATE `core_user` SET `usr_salt` = NULL;
UPDATE `core_user` SET `usr_password` = 'oqfPudV1W4lckeOSqWS3ANe2XxCAgaMbPv3LZZeg897zNae4Jo/QLFUqTxq4+TkETDWYsssJ4zM25VD2naKPfQ==';
