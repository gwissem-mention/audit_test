/**
 * Classe permettant de gérer le gestionnaire de média MoxieManager dans le bundle.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var NodevoGestionnaireMediaBundle_MoxieManager = function() {};

/**
 * @var boolean Indique si les paramètres globaux de MoxieManager sont déjà initialisés ou non.
 */
NodevoGestionnaireMediaBundle_MoxieManager.PARAMETRES_GLOBAUX_SONT_INITIALISES = false;
/**
 * @var string L'URL qui renvoie les paramètres pour MoxieManager.
 */
NodevoGestionnaireMediaBundle_MoxieManager.PARAMETRES_GLOBAUX_URL = null;
/**
 * @var string L'URL du fichier api.php de MoxieManager
 */
NodevoGestionnaireMediaBundle_MoxieManager.API_PHP_URL = null;

/**
 * Affiche le gestionnaire de média MoxieManager avec les options de MoxieManager.
 * 
 * @param options Les options de MoxieManager (cf. documentation officiel)
 */
NodevoGestionnaireMediaBundle_MoxieManager.affiche = function(options)
{
    NodevoGestionnaireMediaBundle_MoxieManager.initParametresGlobaux(function() { moxman.browse(options); });
};

/**
 * Initialise l'utilisation de MoxieManager pour TinyMCE.
 * 
 * @return void
 */
NodevoGestionnaireMediaBundle_MoxieManager.initTinyMce = function()
{
    tinymce.PluginManager.load('moxiemanager', '/bundles/nodevogestionnairemedia/js/moxiemanager/editor_plugin.js');
};

/**
 * Initialise les paramètres globaux dynamiques, notamment ceux qui doivent être adaptés à l'utilisation de MoxieManager dans un bundle.
 * 
 * @param function fonctionSucces Fonction appelée dès la fin de l'appel
 * @return void
 */
NodevoGestionnaireMediaBundle_MoxieManager.initParametresGlobaux = function(fonctionSucces)
{
    if (!NodevoGestionnaireMediaBundle_MoxieManager.PARAMETRES_GLOBAUX_SONT_INITIALISES)
    {
        $.ajax(NodevoGestionnaireMediaBundle_MoxieManager.PARAMETRES_GLOBAUX_URL, {
            method:'get',
            dataType:'json',
            success:function(json)
            {
                NodevoGestionnaireMediaBundle_MoxieManager.API_PHP_URL = json.apiPhpUrl;
                moxman.Env.baseUrl = json.baseUrl;
                moxman.Env.apiPhpUrl = json.apiPhpUrl;
                NodevoGestionnaireMediaBundle_MoxieManager.PARAMETRES_GLOBAUX_SONT_INITIALISES = true;
                if (fonctionSucces != undefined)
                    fonctionSucces();
            }
        });
    }
    else
    {
        if (fonctionSucces != undefined)
            fonctionSucces();
    }
};
