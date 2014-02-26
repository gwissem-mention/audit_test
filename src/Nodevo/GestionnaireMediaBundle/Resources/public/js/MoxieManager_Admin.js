/**
 * Classe permettant de gérer le gestionnaire de média MoxieManager utilisé dans la console administrative.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var NodevoGestionnaireMediaBundle_MoxieManager_Admin = function() {};

/**
 * Affiche le gestionnaire de média MoxieManager en plein écran.
 * 
 * @return void
 */
NodevoGestionnaireMediaBundle_MoxieManager_Admin.affichePleinEcran = function()
{
    NodevoGestionnaireMediaBundle_MoxieManager.affiche({
        fullscreen:true,
        close:false,
        insert:false,
        no_host:true
    });
};