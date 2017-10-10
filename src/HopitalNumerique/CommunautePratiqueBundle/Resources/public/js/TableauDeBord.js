/**
 * Classe gérant les groupes de la communauté de pratique.
 */
var     CommunautePratique_TableauDeBord = function() {};


$(document).ready(function() {
        CommunautePratique_TableauDeBord.init();
});


/**
 * Initialisation.
 */
    CommunautePratique_TableauDeBord.init = function() {
        setTimeout(function() { CommunautePratique_TableauDeBord.fixeHauteurBlocs(); }, 200);
};

/**
 * Fixe la hauteur des blocs de la page Groupes de travail.
 */
CommunautePratique_TableauDeBord.fixeHauteurBlocs = function()
{
    var panelGroupesHeight = $('#panel-communaute-de-pratiques-groupes').height()
        + parseInt($('#panel-communaute-de-pratiques-groupes').css('marginTop'))
        + parseInt($('#panel-communaute-de-pratiques-groupes').css('marginBottom'))
        + parseInt($('#panel-communaute-de-pratiques-groupes').css('paddingTop'))
        + parseInt($('#panel-communaute-de-pratiques-groupes').css('paddingBottom'));
    var panelMesGroupesHeight = $('#panel-communaute-de-pratiques-mes-groupes').height()
        + parseInt($('#panel-communaute-de-pratiques-mes-groupes').css('marginTop'))
        + parseInt($('#panel-communaute-de-pratiques-mes-groupes').css('marginBottom'))
        + parseInt($('#panel-communaute-de-pratiques-mes-groupes').css('paddingTop'))
        + parseInt($('#panel-communaute-de-pratiques-mes-groupes').css('paddingBottom'))
        + $('.communaute-de-pratiques-bloc-publications').height()
        + parseInt($('.communaute-de-pratiques-bloc-publications').css('marginTop'))
        + parseInt($('.communaute-de-pratiques-bloc-publications').css('marginBottom'))
        + parseInt($('.communaute-de-pratiques-bloc-publications').css('paddingTop'))
        + parseInt($('.communaute-de-pratiques-bloc-publications').css('paddingBottom'));
    
    if (panelGroupesHeight > panelMesGroupesHeight) {
        $('#panel-communaute-de-pratiques-mes-groupes').height($('#panel-communaute-de-pratiques-mes-groupes').height() + panelGroupesHeight - panelMesGroupesHeight);
    } else if (panelMesGroupesHeight > panelGroupesHeight) {
        $('#panel-communaute-de-pratiques-groupes').height($('#panel-communaute-de-pratiques-groupes').height() + panelMesGroupesHeight - panelGroupesHeight);
    }
    if($(window).width() > 1000) {
        var panelActualitesHeight = $('#panel-communaute-de-pratiques-actualites').height()
            + parseInt($('#panel-communaute-de-pratiques-actualites').css('marginTop'))
            + parseInt($('#panel-communaute-de-pratiques-actualites').css('marginBottom'))
            + parseInt($('#panel-communaute-de-pratiques-actualites').css('paddingTop'))
            + parseInt($('#panel-communaute-de-pratiques-actualites').css('paddingBottom'));
        var panelForumHeight = $('#panel-communaute-de-pratiques-forums').height()
            + parseInt($('#panel-communaute-de-pratiques-forums').css('marginTop'))
            + parseInt($('#panel-communaute-de-pratiques-forums').css('marginBottom'))
            + parseInt($('#panel-communaute-de-pratiques-forums').css('paddingTop'))
            + parseInt($('#panel-communaute-de-pratiques-forums').css('paddingBottom'));

        if (panelActualitesHeight > panelForumHeight) {
            $('#panel-communaute-de-pratiques-forums').height($('#panel-communaute-de-pratiques-forums').height() + panelActualitesHeight - panelForumHeight);
        } else if (panelForumHeight > panelActualitesHeight) {
            $('#panel-communaute-de-pratiques-actualites').height($('#panel-communaute-de-pratiques-actualites').height() + panelForumHeight - panelActualitesHeight);
        }
    }
};

/**
 * Ouvre / ferme le bloc d'un groupe.
 * 
 * @param groupeId ID du groupe
 * @param callback Callback function
 */
CommunautePratique_TableauDeBord.toggleOuvertureGroupe = function(groupeId, callback)
{
    $('[data-groupe-id=' + groupeId + '] .body').toggle({
        done: function() {
            if ($('[data-groupe-id=' + groupeId + '] .body').css('display') !== 'block') {
                $('[data-groupe-id=' + groupeId + '] .interrupteur').removeClass('on').addClass('off');
            } else {
                $('[data-groupe-id=' + groupeId + '] .interrupteur').addClass('on').removeClass('off');
            }

            if (callback !== undefined) {
                callback();
            }
        }
    });
};
