/**
 * Classe permettant de gérer le gestionnaire de média MoxieManager utilisé dans la frame.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var NodevoGestionnaireMediaBundle_MoxieManager_Frame = function() {};

$(document).ready(function() {
    NodevoGestionnaireMediaBundle_MoxieManager_Frame.init();
});

/**
 * Initialise le gestionnaire de média MoxieManager dans la frame.
 * 
 * @return void
 */
NodevoGestionnaireMediaBundle_MoxieManager_Frame.init = function()
{
    NodevoGestionnaireMediaBundle_MoxieManager_Frame.initAnimationAttente();
    NodevoGestionnaireMediaBundle_MoxieManager_Frame.affichePleinEcran();
};

/**
 * Initialise l'animation d'attente (AJAX loader) le temps que MoxieManager se charge.
 * 
 * @return void
 */
NodevoGestionnaireMediaBundle_MoxieManager_Frame.initAnimationAttente = function()
{
    $('div#moxiemanager_frame').height($(document).height());
    $('div#moxiemanager_frame').nodevoLoader().start();
};

/**
 * Affiche le gestionnaire de média MoxieManager en plein écran.
 * 
 * @return void
 */
NodevoGestionnaireMediaBundle_MoxieManager_Frame.affichePleinEcran = function()
{
    NodevoGestionnaireMediaBundle_MoxieManager.affiche({
        fullscreen:true,
        close:false,
        insert:false,
        no_host:true
    });
};