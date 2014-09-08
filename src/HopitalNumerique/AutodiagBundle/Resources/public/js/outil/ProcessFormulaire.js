/**
 * Classe traitant le formulaire d'un processus d'outil.
 * 
 * @author Rémi Leclerc
 */
var HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire = function() {};

HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.OUTIL_PROCESS_UL = null;
HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.OUTIL_PROCESS_AJOUT_LI = $('<li></li>');


$(document).ready(function() {
    HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.init();
});


/**
 * Initialisation du formulaire d'édition d'un process.
 * 
 * @return void
 */
HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.init = function()
{
    HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.initListeProcesseurs();
    
    HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.OUTIL_PROCESS_UL = $('#outil_process');
    
    HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.OUTIL_PROCESS_UL.find('li').each(function() {
        HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.addSuppressionProcessLien($(this));
    });
    HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.OUTIL_PROCESS_UL.append(HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.OUTIL_PROCESS_AJOUT_LI);
    $('#outil_process_ajout').on('click', function(e) {
        HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.addProcessChamp();
    });
};
/**
 * Initialisation de la liste des processeurs.
 * 
 * @return void
 */
HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.initListeProcesseurs = function()
{
    $('#outil_process_conteneur').nestable({
        group:0
    });
};

/**
 * @var string Valeur initiale du libellé de processus ouvert.
 */
HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.PROCESSUS_LIBELLE_VALEUR = null;
/**
 * @var array Ids des chapitres initiaux du processus ouvert.
 */
HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.PROCESSUS_CHAPITRES_IDS = new Array();
/**
 * Initialisation de la fenêtre d'édition d'un processus.
 * 
 * @param integer numeroProcessus Numéro du formulaire attribué par SF2
 * @return void
 */
HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.initFenetreProcessusEdition = function(numeroProcessus)
{
    HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.PROCESSUS_LIBELLE_VALEUR = $('#hopitalnumerique_autodiag_outil_process_' + numeroProcessus + '_libelle').val();
    HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.PROCESSUS_CHAPITRES_IDS = $('#hopitalnumerique_autodiag_outil_process_' + numeroProcessus + '_chapitres').val();
    $('#outil_processus_ajout_fenetre_' + numeroProcessus).replaceWith($('#hopitalnumerique_autodiag_outil_process_' + numeroProcessus).attr('id', 'outil_processus_ajout_fenetre_' + numeroProcessus).attr('class', 'form-horizontal').css({ display:'block' }));
};
/**
 * ferme la fenêtre d'édition d'un processus.
 * 
 * @param integer numeroProcessus Numéro du formulaire attribué par SF2
 * @return void
 */
HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.fermeFenetreProcess = function(numeroProcessus)
{
    $('#hopitalnumerique_autodiag_outil_process_conteneur_' + numeroProcessus).append($('#outil_processus_ajout_fenetre_' + numeroProcessus).attr('id', 'hopitalnumerique_autodiag_outil_process_' + numeroProcessus));
    //<-- On remet les valeurs initiales
    $('#hopitalnumerique_autodiag_outil_process_' + numeroProcessus + '_libelle').val(HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.PROCESSUS_LIBELLE_VALEUR);
    $('#hopitalnumerique_autodiag_outil_process_' + numeroProcessus + '_chapitres').val(HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.PROCESSUS_CHAPITRES_IDS);
    //-->
    $.fancybox.close(true);
};
/**
 * Enregistre un processus.
 * 
 * @param integer numeroProcessus Numéro du formulaire attribué par SF2
 * @return void
 */
HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.enregistreFenetreProcess = function(numeroProcessus)
{
    $('#hopitalnumerique_autodiag_outil_process_conteneur_' + numeroProcessus).append($('#outil_processus_ajout_fenetre_' + numeroProcessus).attr('id', 'hopitalnumerique_autodiag_outil_process_' + numeroProcessus));
    $('#outil_process_libelle_' + numeroProcessus).html($('#hopitalnumerique_autodiag_outil_process_' + numeroProcessus + '_libelle').val());
    $.fancybox.close(true);
};


/**
 * Ajoute dans le formulaire un lien permettant de supprimer un process.
 * 
 * @param Element elementLi Élément LI que l'on peut supprimer
 * @param integer processusNumero Le numero du formulaire attribué par SF2
 * @return void
 */
HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.addModificationProcessLien = function(elementParent, processusNumero)
{
    var modificationProcessLien = $('<a class="dd3-cog pull-right fancybox.ajax" href="/admin/outil/process/add/' + processusNumero + '"><span class="fa fa-cog"></span></a>');
    
    elementParent.append(modificationProcessLien);
    
    modificationProcessLien.fancybox({
        padding: 0,
        autoSize: false,
        width: '80%',
        height: '400px',
        scrolling: 'yes',
        modal: true
    });
    
    modificationProcessLien.on('click', function(e)
    {
        /*if (HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.OUTIL_PROCESS_UL.children().length > 2)
        {
            if (confirm('Confirmez-vous la suppression de cet élément ?'))
                elementParent.remove();
        }
        else alert('Vous ne pouvez pas supprimer tous les éléments.');*/
    });
};
/**
 * Ajoute dans le formulaire un lien permettant de modifier un process.
 * 
 * @param Element elementLi Élément LI que l'on peut supprimer
 * @return void
 */
HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.addSuppressionProcessLien = function(elementParent)
{
    var suppressionProcessLien = $('<a class="dd3-trash pull-right"><span class="fa fa-trash-o"></span></a>');
    elementParent.append(suppressionProcessLien);
    
    suppressionProcessLien.on('click', function(e)
    {
        if (HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.OUTIL_PROCESS_UL.children().length > 2)
        {
            if (confirm('Confirmez-vous la suppression de cet élément ?'))
                elementParent.parent().remove();
        }
        else alert('Vous ne pouvez pas supprimer tous les éléments.');
    });
};
/**
 * Ajoute dans le formulaire un champ de création de process.
 * 
 * @return void
 */
HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.addProcessChamp = function()
{
    var prototype = HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.OUTIL_PROCESS_UL.attr('data-prototype');
    var nouveauChamp = prototype.replace(/__name__/g, HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.OUTIL_PROCESS_UL.children().length);
    var processusNumero = $(nouveauChamp).attr('id').substr(parseInt((new String('hopitalnumerique_autodiag_outil_process_')).length));

    nouveauChamp = '<div id="hopitalnumerique_autodiag_outil_process_conteneur_' + processusNumero + '">' + nouveauChamp + '</div>';
    var nouveauChampLi = $('<li class="dd-item dd3-item"></li>').append('<div class="dd-handle dd3-handle"></div>').append('<div class="dd3-content" id="outil_process_libelle_' + processusNumero + '"></div>').append(nouveauChamp);
    
    var conteneurActions = $('<div class="dd3-actions"></div>');
    HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.addSuppressionProcessLien(conteneurActions);
    HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.addModificationProcessLien(conteneurActions, processusNumero);
    nouveauChampLi.append(conteneurActions);
    
    HopitalNumeriqueAutodiagBundle_OutilProcessFormulaire.OUTIL_PROCESS_AJOUT_LI.before(nouveauChampLi);
    
    $('#hopitalnumerique_autodiag_outil_process_' + processusNumero + '_chapitres').select2();
};
