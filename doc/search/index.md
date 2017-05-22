# Moteur de recherche

Le moteur de recherche du Centre de ressources et basé sur elasticsearch.
Un middleware permet de traiter les requêtes de recherche, il est construit sur `silex` (`/searchEngine`).
Le front est une application Angular 4.

## Installation
    
### Angular

    $ cd assets/searchengine
    $ cp config/parameters.json.dist config/parameters.json
    # Edit config/parameters.json
    $ npm install

### Symfony

Installer les dépendances via composer et configurer l'application :

- `elastica_host`: Url du serveur Elasticsearch
- `elastica_port`: Port du serveur Elasticsearch
- `searchEngine_app_source_path`: L'url de `webpack-web-server`, utiliser en environement de développement uniquement 

### Silex

Installer les dépendances via composer, à partir du répertoire `/searchEngine`.


## Développement

En phase de développement, l'application front est servie par `webpack-dev-server`.

    $ cd assets/searchengine
    $ npm start    

## Production

En production, il faut `build` l'application Angular comme suit :

    $ cd assets/searchengine
    $ npm run build

Les assets sont alors générés dans `/web/dist`.