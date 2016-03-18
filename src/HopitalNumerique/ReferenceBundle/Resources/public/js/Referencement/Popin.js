/**
 * Classe gérant la fenêtre de référencement.
 * 
 * @author Rémi Leclerc
 */
var Hn_Reference_Referencement_Popin = function() {};


$(document).ready(function() {
    Hn_Reference_Referencement_Popin.initEvents();
});

/**
 * Initialise les événements.
 */
Hn_Reference_Referencement_Popin.initEvents = function()
{
    $('.referencement-popin #toggle-check-all').click(function () {
        Hn_Reference_Referencement_Popin.toggleCheckAll();
    });
    $('.referencement-popin input[type="checkbox"][id!="toggle-check-all"]').click(function (event) {
        Hn_Reference_Referencement_Popin.checkReference_click(event);
    });
};


/**
 * Affiche / cache les lignes de sous-référence.
 *
 * @param integer referenceParentId ID du parent des sous-références
 */
Hn_Reference_Referencement_Popin.toggleDisplayingReferencesSubtree = function(referenceParentId, open)
{
    var referenceParentLine = $('tr[data-reference="' + referenceParentId + '"]');
    var referencesLines = $('tr[data-reference-parent="' + referenceParentId + '"]');
    var haveToOpen = (undefined === open ? ('1' !== referenceParentLine.attr('data-children-open')) : open);

    referenceParentLine.attr('data-children-open', (haveToOpen ? '1' : '0'));
    referenceParentLine.find('td:last-child .fa').removeClass(haveToOpen ? 'fa-arrow-right' : 'fa-arrow-down');
    referenceParentLine.find('td:last-child .fa').addClass(haveToOpen ? 'fa-arrow-down' : 'fa-arrow-right');

    if (referencesLines.size() > 0) {
        $(referencesLines).each(function (key, referenceLine) {
            var referenceEnfantId = $(referenceLine).attr('data-reference');
            if (haveToOpen) {
                $(referenceLine).show();
            } else {
                $(referenceLine).hide();
                Hn_Reference_Referencement_Popin.toggleDisplayingReferencesSubtree(referenceEnfantId, false);
            }
        });
    }
};

/**
 * Coche / décoche toutes les cases.
 */
Hn_Reference_Referencement_Popin.toggleCheckAll = function()
{
    if ($('.referencement-popin #toggle-check-all').is(':checked')) {
        Nodevo_Form_Box.checkAll('.referencement-popin');
    } else {
        Nodevo_Form_Box.uncheckAll('.referencement-popin');
    }
};

/**
 * Événement au clic sur la case d'une référence.
 */
Hn_Reference_Referencement_Popin.checkReference_click = function(event)
{
    var checkbox = $(event.target);
    var isChecked = checkbox.is(':checked');

    Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren(parseInt(checkbox.attr('data-reference')), isChecked);
    if (isChecked) {
        Hn_Reference_Referencement_Popin.checkAllReferenceParent(parseInt(checkbox.attr('data-reference')));
    }
};

/**
 * Coche toutes les références enfants.
 *
 * @param integer referenceParentId ID de la référence parente
 * @param boolean check             Si doivent être cochées ou décochées
 */
Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren = function(referenceParentId, check)
{
    $('tr[data-reference-parent="' + referenceParentId + '"] input[type="checkbox"]').each(function (key, checkbox) {
        $(checkbox).prop('checked', check);
        if (check) {
            Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren(parseInt($(checkbox).attr('data-reference')), check);
        } else {
            Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren(parseInt($(checkbox).attr('data-reference')), check);
        }
    });
};

/**
 * Coche toutes les références parentes.
 *
 * @param integer referenceId ID de la référence
 */
Hn_Reference_Referencement_Popin.checkAllReferenceParent = function(referenceId)
{
    var referenceParentId = $('tr[data-reference="' + referenceId + '"]').attr('data-reference-parent');

    if ('' !== referenceParentId) {
        $('tr[data-reference="' + referenceParentId + '"] input[type="checkbox"]').prop('checked', true);
        Hn_Reference_Referencement_Popin.checkAllReferenceParent(referenceParentId);
    }
};


/**
 * Retourne si la référence a au moins un enfant coché.
 *
 * @param integer referenceId ID de la référence
 * @returns boolean Si enfant
 */
Hn_Reference_Referencement_Popin.hasCheckedReferenceChildren = function(referenceId)
{
    var referenceChildrenCheckboxes = $('tr[data-reference-parent="' + referenceId + '"] input[type="checkbox"]');
    var hasCheckedReferenceChildren = false;

    $(referenceChildrenCheckboxes).each(function (i, referenceCheckbox) {
        if ($(referenceCheckbox).is(':checked') || Hn_Reference_Referencement_Popin.hasCheckedReferenceChildren($(referenceCheckbox).attr('data-reference'))) {
            hasCheckedReferenceChildren = true;
            return false;
        }
    });

    return hasCheckedReferenceChildren;
};

/**
 * Retourne les ID de références choisies par l'utilisateur.
 *
 * @param {type} referenceId
 * @returns {undefined}
 */
Hn_Reference_Referencement_Popin.getChosenReferenceIds = function(referenceParentId, alreadyChosenReferenceIds)
{
    if (undefined === referenceParentId) {
        referenceParentId = '';
    }
    if (undefined === alreadyChosenReferenceIds) {
        alreadyChosenReferenceIds = new Array();
    }

    var referenceChildrenCheckboxes = $('tr[data-reference-parent="' + referenceParentId + '"] input:checked');
    //alerte(referenceChildrenCheckboxes)
    $(referenceChildrenCheckboxes).each(function (i, referenceCheckbox) {
        var referenceId = $(referenceCheckbox).attr('data-reference');
        // Si une référence enfant est cochée, on ne récupère pas son parent
        if (Hn_Reference_Referencement_Popin.hasCheckedReferenceChildren(referenceId)) {
            alreadyChosenReferenceIds = alreadyChosenReferenceIds.concat(Hn_Reference_Referencement_Popin.getChosenReferenceIds(referenceId));
        } else {
            alreadyChosenReferenceIds.push(referenceId);
        }
    });

    return alreadyChosenReferenceIds;
};

