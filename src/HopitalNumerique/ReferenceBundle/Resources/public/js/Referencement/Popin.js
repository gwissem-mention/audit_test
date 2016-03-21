/**
 * Classe gérant la fenêtre de référencement.
 * 
 * @author Rémi Leclerc
 */
var Hn_Reference_Referencement_Popin = function() {};


/**
 * @var string Type d'entité
 */
Hn_Reference_Referencement_Popin.ENTITY_TYPE = null;

/**
 * @var integer ID de l'entité
 */
Hn_Reference_Referencement_Popin.ENTITY_ID = null;


/**
 * Initialise la fenêtre.
 */
Hn_Reference_Referencement_Popin.init = function()
{
    Hn_Reference_Referencement_Popin.initEvents();
    $('.referencement-popin input[data-initial-checked="1"]').each(function (i, referenceCheckbox) {
        Hn_Reference_Referencement_Popin.setReference($(referenceCheckbox).attr('data-reference'));
    });

    $('.toggle.on').toggles( { on : true, text : { on : 'OUI', off : 'NON' }, type : 'select' } );
    $('.toggle.off').toggles( { on : false, text : { on : 'OUI', off : 'NON' }, type : 'select' } );
};

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
    $('.referencement-popin [data-action="save"]').click(function (event) {
        Hn_Reference_Referencement_Popin.saveEntitiesHaveReferencesAndClose();
    });
};


/**
 * Affiche / cache les lignes de sous-référence.
 *
 * @param integer referenceParentId ID du parent des sous-références
 * @param boolean open              (facultatif) S'il faut afficher ou cacher les lignes
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
    var referenceId = parseInt($(checkbox).attr('data-reference'));
    var isChecked = checkbox.is(':checked');

    Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren(referenceId, isChecked);
    if (isChecked) {
        Hn_Reference_Referencement_Popin.checkAllReferenceParent(referenceId);
    }
    Hn_Reference_Referencement_Popin.refreshCountElements(referenceId);
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
 * Retourne les paramètres des EntityHasReference choisies par l'utilisateur.
 *
 * @param integer         referenceParentId        (facultatif) ID de la référence parente (noeud root par défaut)
 * @param array<integer> alreadyChosenEntitiesHaveReferences IDs des références déjà choisies
 * @return array<integer> IDs des références
 */
Hn_Reference_Referencement_Popin.getChosenEntitiesHaveReferences = function(referenceParentId, alreadyChosenEntitiesHaveReferences)
{
    if (undefined === referenceParentId) {
        referenceParentId = '';
    }
    if (undefined === alreadyChosenEntitiesHaveReferences) {
        alreadyChosenEntitiesHaveReferences = new Array();
    }

    var referenceChildrenCheckboxes = $('tr[data-reference-parent="' + referenceParentId + '"] input:checked');

    $(referenceChildrenCheckboxes).each(function (i, referenceCheckbox) {
        var referenceId = $(referenceCheckbox).attr('data-reference');

        // Si une référence enfant est cochée, on ne récupère pas son parent
        if (Hn_Reference_Referencement_Popin.hasCheckedReferenceChildren(referenceId)) {
            alreadyChosenEntitiesHaveReferences = alreadyChosenEntitiesHaveReferences.concat(Hn_Reference_Referencement_Popin.getChosenEntitiesHaveReferences(referenceId));
        } else {
            alreadyChosenEntitiesHaveReferences.push({
                'referenceId': referenceId,
                'primary': (Hn_Reference_Referencement_Popin.getPrimaryForReferenceId(referenceId) ? '1' : '0')
            });
        }
    });

    return alreadyChosenEntitiesHaveReferences;
};

/**
 * Coche les cases correspondant à un ID de référence.
 * 
 * @param integer referenceId ID de la référence
 */
Hn_Reference_Referencement_Popin.setReference = function(referenceId)
{
    var referenceCheckbox = $('tr[data-reference="' + referenceId + '"] input[type="checkbox"]');
    if (!$(referenceCheckbox).is(':checked')) {
        $(referenceCheckbox).click();
        Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren(referenceId, false);
    }
};

/**
 * Set primary pour la référence.
 *
 * @param boolean primary     Primary
 * @param integer referenceId ID de référence
 */
Hn_Reference_Referencement_Popin.setPrimaryForReferenceId = function(primary, referenceId)
{
    $('tr[data-reference="' + referenceId + '"] .toggle').removeClass(primary ? 'off' : 'on');
    $('tr[data-reference="' + referenceId + '"] .toggle').addClass(primary ? 'on' : 'off');
};

/**
 * Retourne primary pour la référence.
 *
 * @param integer referenceId ID de référence
 */
Hn_Reference_Referencement_Popin.getPrimaryForReferenceId = function(referenceId)
{
    return ($('tr[data-reference="' + referenceId + '"] .toggle-slide').hasClass('active'));
};

/**
 * Enregistre les choix de l'utilisateur.
 */
Hn_Reference_Referencement_Popin.saveEntitiesHaveReferencesAndClose = function()
{
    $.ajax({
        url: Routing.generate(
            'hopitalnumerique_reference_referencement_savechosenreferences',
            {
                'entityType':Hn_Reference_Referencement_Popin.ENTITY_TYPE,
                'entityId':Hn_Reference_Referencement_Popin.ENTITY_ID
            }
        ),
        data: {
            entitiesHaveReferencesParameters: Hn_Reference_Referencement_Popin.getChosenEntitiesHaveReferences()
        },
        type: 'post',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Nodevo_Web.reload();
            }
        }
    });
};

/**
 * Affiche le nombre d'éléments d'une référence et de tous ses parents dans le tableau.
 *
 * @param integer referenceId ID de la référence
 */
Hn_Reference_Referencement_Popin.refreshCountElements = function(referenceId)
{
    var checkedChildren = $('tr[data-reference-parent="' + referenceId + '"] input:checked');

    $('tr[data-reference="' + referenceId + '"] .count-checked-children').html($(checkedChildren).size());

    var referenceParentId = $('tr[data-reference="' + referenceId + '"]').attr('data-reference-parent');
    if ('' != referenceParentId) {
        Hn_Reference_Referencement_Popin.refreshCountElements(referenceParentId);
    }
};
