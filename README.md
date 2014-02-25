test
h1. README

> Rédigé par Quentin Somazzi le 19/01/2014.
> MAJ par Gaëtan Melchilsen le 18/02/2014.

* +Url du SVN+ : http://local.nodevo.com/usvn/svn/wwwhopitalnumeriquecom/trunk/
* +Serveur de la base de donnée de dev+ : local.nodevo.com
* +Nom de la base de donnée de dev+ : wwwhopitalnumeriquecom
* +Url de preprod+ : http://preprod.hopitalnumerique.nodevo.com/

h2. 1. Comment récupérer et installer le projet Hopital Numérique pour la première fois

* Mettez à jour votre projet via composer
<pre><code class="php"> composer install
</code></pre>
* Par défaut, votre fichier Paramètres personnel va se construire à l'installation, si vous désirez changer les paramètres (mot de passe entre autres), modifiez ces champs lorsque qu'il vous seront demandés à la fin de l'installation, sinon laissez ceux par défaut, tout devrait fonctionner
* Videz les dossiers cache et logs
<pre><code class="php"> rm -rf app/cache/* app/logs/*
</code></pre>
* Mettez les droits sur ces dossiers
<pre><code class="php"> sudo chmod -R 777 app/cache app/logs
</code></pre>
* Créez un Vhost pour votre application (exemple de vhost en 2)
* Ajouter hopital.local dans votre fichier /etc/hosts
* Dumpez vos assets
<pre><code class="php"> php app/console assets:install --symlink
php app/console assetic:dump
</code></pre>

h2. 2 . Vhost recommandé pour le projet

<pre><code class="php">
<VirtualHost *:80>
    DocumentRoot /var/www/wwwhopitalnumeriquecom/web/
    ServerName hopital.local

    <Directory "/var/www/wwwhopitalnumeriquecom/web/">
        AllowOverride None
        DirectoryIndex app_dev.php
        Order allow,deny
        Allow from all
        Options Indexes FollowSymLinks SymLinksifOwnerMatch

        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ app_dev.php [QSA,L]
    </Directory>
</VirtualHost>
</code>
</pre>


h2. 3 . Points sensibles à prendre en compte lors des différents développements

* La suppression des utilisateurs supprime tous les éléments liés (intégrité de la base de donnée) => à vérifier à chaque ajout de liaison.
* Il ne peut exister qu'un compte de type ARS - CMSI par région
* Il ne peut exister qu'un compte de type ES - Direction générale par établissement de rattachement
* Suppression Etablissement si aucun utilisateur lié
* La gestion des groupes des utilisateurs est réalisée selon les ID des groupes, cette valeur n'étant pas censée être modifiée, elle est notée en dur dans l'entité Role
* Attention à la sécurité avec moximanager, seuls les personnes habilitées dans le système de gestion des habilitations peuvent y accéder. Attention également à l'initialisation du moxi en console JS.
* L'upload de fichier lié aux Objet parse le nom du fichier uploadé ( le nom diffère selon le navigateur c:\fakepath\filename ou filename ).
* Ajout de la fonction native '__toString()' dans l'entité Utilisateur. Permet de recupérer l'id dans les reqûetes sur les jointures.

h2. 4 . Snippets utiles au projet 

<pre><code class="php">
//Extension Twig qui vérifie si l'user connecté à le droit d'accéder à la ressource
{% if app.user|checkAuthorization( path('nom_route') ) %}

{% endif %}
</code>
</pre>

h2. 5. Conventions de nommage pour le projet

* Si les actions du CRUD sont nécessaires, utilisez les actions suivantes : indexAction, editAction, viewAction, addAction, deleteAction.
* Ne pas mettre d'accents sur toutes les majuscules.
* Utilisez les termes suivants :
** Liste des __  : vue liste/grid
** Ajouter un __ : formulaire d'ajout
** Editer un __ : formulaire d'édition
** Enregistrer (et pas sauvegarder)
* Dans les vues de grid (titres des colonnes) et dans les formulaires ne pas préciser : Element du __  (ex : mettre *Nom* à la place de *Nom du groupe* )

h2. 5. Droits sur les dossiers

Les dossier suivants (et enfants) doivent être accessible en écriture pour l'utilisateur apache :
- app/cache/
- app/logs/
- files/
- web/medias
