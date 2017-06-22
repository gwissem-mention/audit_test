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
 * @var string URL de redirection
 */
Hn_Reference_Referencement_Popin.REDIRECTION_URL = null;

/**
 * @var boolean Indique si un clic est en cours sur un toggle
 */
Hn_Reference_Referencement_Popin.TOGGLE_PROCESS_CLICK = false;


$(document).ready(function () {
    Hn_Reference_Referencement_Popin.preparePopin();
});

/**
 * Initialise Fancybox.
 */
Hn_Reference_Referencement_Popin.preparePopin = function()
{
    $('.open-popin-referencement').click(function (e) {
        e.preventDefault();
        Hn_Reference_Referencement_Popin.open($(this).attr('href'));
    });
};

Hn_Reference_Referencement_Popin.open = function (url)
{
    $.fancybox.open({
        padding   : 0,
        autoSize  : false,
        width     : '80%',
        scrolling : 'auto',
        modal     : true,
        href      : url,
        type      : 'ajax'
    });
};

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
    $('.referencement-popin [data-action="save"]').click(function () {
        Hn_Reference_Referencement_Popin.saveEntitiesHaveReferencesAndClose();
    });
    $('.referencement-popin .toggle').click(function (event) {
        if (Hn_Reference_Referencement_Popin.TOGGLE_PROCESS_CLICK) {
            event.preventDefault();
        } else {
            Hn_Reference_Referencement_Popin.toggle_click(event);
        }
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
    var referenceParentId = $(checkbox).attr('data-reference-parent');
    var isChecked = checkbox.is(':checked');

    Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren(referenceId, referenceParentId, isChecked);
    if (isChecked) {
        Hn_Reference_Referencement_Popin.checkAllReferenceParent(referenceId, referenceParentId);
    }
    Hn_Reference_Referencement_Popin.refreshCountElements(referenceId);
    Hn_Reference_Referencement_Popin.refreshPrimaryChoiceDisplaying();

    //<-- Simuler le clic sur tous les éléments frères (même référence mais parent différent)
    $('tr[data-reference="' + referenceId + '"] input[type="checkbox"]').each(function (i, checkboxWithSameReference) {
        if (isChecked !== $(checkboxWithSameReference).is(':checked')) {
            $(checkboxWithSameReference).click();
        }
    });
    //-->
};

/**
 * Coche toutes les références enfants.
 *
 * @param integer referenceParentId ID de la référence parente
 * @param boolean check             Si doivent être cochées ou décochées
 */
Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren = function(referenceParentId, referenceGrandParentId, check)
{
    $('tr[data-reference-parent="' + referenceParentId + '"][data-reference-grand-parent="' + referenceGrandParentId + '"] input[type="checkbox"]').each(function (key, checkbox) {
        if (check != $(checkbox).is(':checked')) {
            $(checkbox).click();
        }

        if (check) {
            Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren(parseInt($(checkbox).attr('data-reference')), referenceParentId, check);
        } else {
            Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren(parseInt($(checkbox).attr('data-reference')), referenceParentId, check);
        }
    });
};

/**
 * Coche toutes les références parentes.
 *
 * @param integer referenceId ID de la référence
 */
Hn_Reference_Referencement_Popin.checkAllReferenceParent = function(referenceId, referenceParentId)
{
    var referenceGrandParentId = $('tr[data-reference="' + referenceId + '"][data-reference-parent="' + referenceParentId + '"]').attr('data-reference-grand-parent');

    if ('' !== referenceParentId) {
        $('tr[data-reference="' + referenceParentId + '"][data-reference-parent="' + referenceGrandParentId + '"] input[type="checkbox"]').prop('checked', true);
        Hn_Reference_Referencement_Popin.checkAllReferenceParent(referenceParentId, referenceGrandParentId);
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
            var newChosenEntitiesHaveReferencesToConcat = Hn_Reference_Referencement_Popin.getChosenEntitiesHaveReferences(referenceId);
            for (var i in newChosenEntitiesHaveReferencesToConcat) {
                if (!Hn_Reference_Referencement_Popin.chosenEntitiesHaveReferencesHasReference(alreadyChosenEntitiesHaveReferences, newChosenEntitiesHaveReferencesToConcat[i].referenceId)) {
                    alreadyChosenEntitiesHaveReferences.push(newChosenEntitiesHaveReferencesToConcat[i]);
                }
            }

            //alreadyChosenEntitiesHaveReferences = alreadyChosenEntitiesHaveReferences.concat(Hn_Reference_Referencement_Popin.getChosenEntitiesHaveReferences(referenceId));
        } else if (!Hn_Reference_Referencement_Popin.chosenEntitiesHaveReferencesHasReference(alreadyChosenEntitiesHaveReferences, referenceId)) {
            alreadyChosenEntitiesHaveReferences.push({
                'referenceId': referenceId,
                'primary': (Hn_Reference_Referencement_Popin.getPrimaryForReferenceId(referenceId, referenceParentId) ? '1' : '0')
            });
        }
    });

    return alreadyChosenEntitiesHaveReferences;
};

/**
 * Retourne si le tableau de EntityHasReference en possède un pour telle référence.
 *
 * @param Array   chosenEntitiesHaveReferences EntitiesHaveReferences
 * @param integer referenceId                  ID de la référence
 * @return boolean Si présent
 */
Hn_Reference_Referencement_Popin.chosenEntitiesHaveReferencesHasReference = function(alreadyChosenEntitiesHaveReferences, referenceId)
{
    var isPresent = false;

    for (var i in alreadyChosenEntitiesHaveReferences) {
        if (referenceId == alreadyChosenEntitiesHaveReferences[i].referenceId) {
            isPresent = true;
            break;
        }
    }

    return isPresent;
};

/**
 * Coche les cases correspondant à un ID de référence.
 * 
 * @param integer referenceId ID de la référence
 */
Hn_Reference_Referencement_Popin.setReference = function(referenceId)
{
    // On ne prend que la première checkbox car s'il y a des références doublons, elles seront automatiquement cochées
    var referenceCheckbox = $('tr[data-reference="' + referenceId + '"] input[type="checkbox"]').get(0);

    if (!$(referenceCheckbox).is(':checked')) {
        $(referenceCheckbox).click();
        Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren(referenceId, $(referenceCheckbox).attr('data-reference-parent'), false);
    }
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
                if (null !== Hn_Reference_Referencement_Popin.REDIRECTION_URL) {
                    Nodevo_Web.redirect(Hn_Reference_Referencement_Popin.REDIRECTION_URL);
                } else {
                    Nodevo_Web.reload();
                }
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

/**
 * Affiche le nombre d'éléments d'une référence et de tous ses parents dans le tableau.
 *
 * @param integer referenceId ID de la référence
 */
Hn_Reference_Referencement_Popin.refreshPrimaryChoiceDisplaying = function(referenceId, referenceParentId)
{
    if (undefined === referenceId) {
        referenceId = '';
        referenceParentId = '';
    }
    var referenceLine = $('tr[data-reference="' + referenceId + '"][data-reference-parent="' + referenceParentId + '"]');
    var referenceChildrenLines = $('tr[data-reference-parent="' + referenceId + '"][data-reference-grand-parent="' + referenceParentId + '"]');
    var referenceIsChecked = (1 === $(referenceLine).find('input:checked').size());
    var hasCheckedChildren = ($(referenceChildrenLines).find('input:checked').size() > 0);

    if (referenceIsChecked) {
        $(referenceLine).find('.toggle').css({ display:(hasCheckedChildren ? 'none' : 'block') });
    } else {
        $(referenceLine).find('.toggle').css({ display:'none' });
    }

    $(referenceChildrenLines).each(function (i, referenceChildLine) {
        Hn_Reference_Referencement_Popin.refreshPrimaryChoiceDisplaying($(referenceChildLine).attr('data-reference'), $(referenceChildLine).attr('data-reference-parent'));
    });
};


//<-- Toggles
/**
 * Événement d'un clic sur un toggle.
 *
 * @param Event event Event
 */
Hn_Reference_Referencement_Popin.toggle_click = function(event)
{
    Hn_Reference_Referencement_Popin.TOGGLE_PROCESS_CLICK = true;

    var toggle = $(event.target);
    var referenceId = Hn_Reference_Referencement_Popin.getReferenceIdByElement(toggle);
    var referenceParentId = Hn_Reference_Referencement_Popin.getReferenceParentIdByElement(toggle);
    var toggleBros = $('tr[data-reference="' + referenceId + '"] .toggle');
    var toggleIsChecked = !Hn_Reference_Referencement_Popin.getPrimaryForReferenceId(referenceId, referenceParentId);

    $(toggleBros).each(function (i, toggleBrother) {
        var toggleBrotherReferenceParentId = Hn_Reference_Referencement_Popin.getReferenceParentIdByElement(toggleBrother);
        if (referenceParentId != toggleBrotherReferenceParentId) {
            Hn_Reference_Referencement_Popin.setPrimaryForReferenceId(toggleIsChecked, referenceId, toggleBrotherReferenceParentId);
        }
    });

    Hn_Reference_Referencement_Popin.TOGGLE_PROCESS_CLICK = false;
};

/**
 * Set primary pour la référence.
 *
 * @param boolean primary     Primary
 * @param integer referenceId ID de référence
 */
Hn_Reference_Referencement_Popin.setPrimaryForReferenceId = function(primary, referenceId, referenceParentId)
{
    if (primary !== Hn_Reference_Referencement_Popin.getPrimaryForReferenceId(referenceId, referenceParentId)) {
        $('tr[data-reference="' + referenceId + '"][data-reference-parent="' + referenceParentId + '"] .toggle').click();
    }
};

/**
 * Retourne primary pour la référence.
 *
 * @param integer referenceId ID de référence
 */
Hn_Reference_Referencement_Popin.getPrimaryForReferenceId = function(referenceId, referenceParentId)
{
    return ($('tr[data-reference="' + referenceId + '"][data-reference-parent="' + referenceParentId + '"] .toggle-slide').hasClass('active'));
};
//-->


//<-- Récupération des références
/**
 * Retourne l'ID de la référence de l'élément du DOM.
 *
 * @param Element element Élément
 */
Hn_Reference_Referencement_Popin.getReferenceIdByElement = function(element)
{
    return Hn_Reference_Referencement_Popin.getReferenceAttributeByElement(element, 'data-reference');
};

/**
 * Retourne l'ID de la référence parente de l'élément du DOM.
 *
 * @param Element element Élément
 */
Hn_Reference_Referencement_Popin.getReferenceParentIdByElement = function(element)
{
    return Hn_Reference_Referencement_Popin.getReferenceAttributeByElement(element, 'data-reference-parent');
};

/**
 * Retourne la référence d'attribut de l'élément du DOM.
 *
 * @param Element element Élément
 */
Hn_Reference_Referencement_Popin.getReferenceAttributeByElement = function(element, attribute)
{
    var referenceId = $(element).attr(attribute);
    if (typeof referenceId !== typeof undefined && false !== referenceId) {
        return referenceId;
    }

    var elementParent = $(element).parent();
    return Hn_Reference_Referencement_Popin.getReferenceAttributeByElement(elementParent, attribute);
};
//-->
