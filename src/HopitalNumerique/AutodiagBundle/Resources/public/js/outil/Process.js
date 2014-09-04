/**
 * Gestion du mode de restitution.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var HopitalNumeriqueAutodiagBundle_OutilProcess = function() {};

$(document).ready(function() {
    HopitalNumeriqueAutodiagBundle_OutilProcess.init();
});


/**
 * Initialise le fonctionnement des processus.
 * 
 * @return void
 */
HopitalNumeriqueAutodiagBundle_OutilProcess.init = function()
{
    HopitalNumeriqueAutodiagBundle_OutilProcess.initEvenements();
};
/**
 * Initialise les événements des processus.
 * 
 * @return void
 */
HopitalNumeriqueAutodiagBundle_OutilProcess.initEvenements = function()
{
    HopitalNumeriqueAutodiagBundle_OutilProcess.initOuvertureFormulaireAjoutProcess();
};
/**
 * Initialise les événements des processus.
 * 
 * @return void
 */
HopitalNumeriqueAutodiagBundle_OutilProcess.initOuvertureFormulaireAjoutProcess = function()
{
    $('#autodiag_process_ajout').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'height'    : '400px',
        'scrolling' : 'yes',
        'modal'     : true
    });
};


/**
 * Affiche le formulaire permettant d'ajouter un processus à un outil.
 * 
 * @return void
 */
/*HopitalNumeriqueAutodiagBundle_OutilProcess.afficheFormulaireAjoutProcessus = function()
{
    $('#autodiag_process_ajout').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'height'    : '600px',
        'scrolling' : 'no',
        'modal'     : true
    });
    
    $.ajax({
        url: '/admin/outil/process/add',
        type: 'POST',
        success: function(html)
        {
            $('#outil_process').html(html);
        }
    });
    return false;
};*/
