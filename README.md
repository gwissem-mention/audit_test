Hopital Numérique
=================

/* Bootstrap Fonts */
ln -s /full/path/to/website/app/Resources/components/bootstrap/dist/fonts/ /full/path/to/website/web/fonts


Pré-requis
==========

- Installer pandoc + MAJ parameters.yml (requis pour l'import Word depuis le back-office)
- Configurer le serveur mysql : SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
- Lancer le service elasticsearch

#Commande pour ubuntu :

HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx|[g]melchilsen' | grep -v root | head -1 | cut -d\  -f1`;
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs;
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs;
make cldroit


Moteur de recherche
===================

[Documentation moteur de rechecher](doc/search/index.md)