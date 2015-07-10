Hopital Numérique
=================

/* Bootstrap Fonts */
ln -s /full/path/to/website/app/Resources/components/bootstrap/dist/fonts/ /full/path/to/website/web/fonts


#Commande pour ubuntu :

HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx|[g]melchilsen' | grep -v root | head -1 | cut -d\  -f1`;
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs;
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs;
make cldroit