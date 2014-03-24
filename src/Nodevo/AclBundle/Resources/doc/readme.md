h1. Nodevo\AclBundle

_Rédigé par Quentin SOMAZZI le 14/02/2014_

Le bundle ACL est un bundle de gestion des habilitations pour Symfony2. Il fournit via une interface simple un tableau qui permet de donner (ou révoquer) l'accès à une fonctionnalité selon le rôle de l'utilisateur connecté.

h2. Dépendences

* \Nodevo\RoleBundle
* \Nodevo\GridBundle

h2. Comment utiliser ce bundle

h3. Les ressources

La liste des Ressources n'est pas administrable depuis l'interface web, il s'agit uniquement d'entrées dans la base de donnée. 
Seuls les développeurs sont censés manipuler ces ressources. Dans l'idéal, il faut garder en tête : *Une ressource = Une fonctionnalité*.
La vérification de l'Acl s'effectue avec un pattern de l'URL de la fonctionnalité.

+Par exemple+, pour la fonctionnalité : Gestion des utilisateurs, nous aurons une entrée de ressource. La gestion des utilisateurs correspond à un UserBundle dont les URLs sont préfixé de 'user'.
Le pattern de la ressource Gestion des utilisateurs sera donc :
<pre><code class="php">
/^\/user/
</code></pre>


h3. L'extension Twig

Afin de vérifier que l'utilisateur connecté à bien l'accès à un élément de la page en lecture ou en écriture, on utilise une extension twig qui permet de vérifier cela.
Il suffit de  passer *l'URL* de l'action à vérifier dans le filtre twig, qui retourne TRUE si l'accès est autorisé, FALSE sinon.

+Par exemple+, pour vérifier que l'utilisateur connecté à bien le droit d'ajouter des groupes, on entoureras d'une condition le bouton d'ajout en twig comme sur l'exemple suivant 
<pre><code class="twig">
{% if app.user|checkAuthorization( path('nodevo_role_add') ) %}
    <a href="{{path('nodevo_role_add')}}" class="btn btn-default" title="Ajouter un groupe"><i class="fa fa-plus"></i></a>
{% endif %}
</code></pre>

h3. Les mots clés

Afin de déterminer si l'URL est de type *Lecture* ou *Ecriture*, le bundle se base sur des mots clés qui sont pour le moment configurés en dur dans le bundle.
Voici la liste des mots clés :

* READ : show, view, liste, list
* WRITE : create, edit, delete, modify, new, update, add

h2. MCD

h2. Améliorations prévues
 
* Mettre la liste des mots clés en config, ainsi pour chaque projet, il sera facile d'ajouter/modifier les mots clés des Acls

h2. Changelog

<pre>v1.0.0 : QSO le 14/02/2014
Mise en place du bundle.
</pre>