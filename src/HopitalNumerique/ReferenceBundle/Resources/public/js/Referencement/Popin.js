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
Hn_Reference_Referencement_Popin.REDIRECTION_URL = window.location;

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

    Hn_Reference_Referencement_Popin.refreshCountElements();
    Hn_Reference_Referencement_Popin.refreshPrimaryChoiceDisplaying();

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
    $('.referencement-popin input[type="checkbox"][id!="toggle-check-all"]').change(function (event) {
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
Hn_Reference_Referencement_Popin.toggleDisplayingReferencesSubtree = function(element)
{
    var reference = element.dataset.reference;
    var level = parseInt(element.dataset.level);
    var children = document.querySelectorAll('[data-level="' + (level + 1) + '"][data-reference-parent="' + reference + '"');

    var isVisible = false;

    for (var i = 0; i < children.length; ++i) {
        if ($(children[i]).is(':visible')) {
            isVisible = false;
            $(children[i]).hide();

            var next = children[i].nextElementSibling;

            while (null !== next && next.dataset.level > children[i].dataset.level) {
                $(next).hide()
                next = next.nextElementSibling;
            }
        } else {
            isVisible = true;
            $(children[i]).show()
        }
    }

    if (isVisible) {
        $(element).find('td:last-child .fa').removeClass('fa-arrow-right');
        $(element).find('td:last-child .fa').addClass('fa-arrow-down');
    } else {

        $(element).find('td:last-child .fa').removeClass('fa-arrow-down');
        $(element).find('td:last-child .fa').addClass('fa-arrow-right');
    }

    return;
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
    var justChecked = checkbox.is(':checked');

    Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren(referenceId, referenceParentId, justChecked);
    if (justChecked) {
        Hn_Reference_Referencement_Popin.checkAllReferenceParent(referenceId, referenceParentId);
    }
    Hn_Reference_Referencement_Popin.refreshCountElements();
    Hn_Reference_Referencement_Popin.refreshPrimaryChoiceDisplaying();
};

/**
 * Coche toutes les références enfants.
 *
 * @param integer referenceParentId ID de la référence parente
 * @param boolean check             Si doivent être cochées ou décochées
 */
Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren = function(referenceParentId, referenceGrandParentId, check)
{
    $('[data-reference="' + referenceParentId + '"]').each(function(key, element) {
        var level = parseInt($(this).data('level'));

        var currentElement = $(this).next();

        // check all next siblings in a lower level of parent element
        while (currentElement.length > 0 && parseInt(currentElement.data('level')) > level) {
            currentElement.find('input[type="checkbox"]').get(0).checked = check;
            currentElement = currentElement.next();
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
        referenceCheckbox.checked = true;
        Hn_Reference_Referencement_Popin.checkOrUncheckAllReferenceChildren(referenceId, $(referenceCheckbox).attr('data-reference-parent'), true);
        Hn_Reference_Referencement_Popin.checkAllReferenceParent(referenceId, $(referenceCheckbox).attr('data-reference-parent'));
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
                    $.fancybox.close();
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
    if (undefined === referenceId) {
        var lines = document.querySelectorAll('.referencement-popin tr[data-reference]');

        for (var i = 0; i < lines.length ; i++) {
            var line = lines[i];
            var next = line.nextElementSibling;
            var count = 0;
            while (next && parseInt(next.dataset.level) >= (parseInt(line.dataset.level) + 1)) {
                if (parseInt(next.dataset.level) === (parseInt(line.dataset.level) + 1)) {
                    count += next.querySelector('input[type="checkbox"]').checked ? 1 : 0;
                }
                next = next.nextElementSibling;
            }

            line.querySelector('.count-checked-children') && (line.querySelector('.count-checked-children').innerHTML = count);
        }
    }
};

/**
 * Affiche le nombre d'éléments d'une référence et de tous ses parents dans le tableau.
 *
 * @param integer referenceId ID de la référence
 */
Hn_Reference_Referencement_Popin.refreshPrimaryChoiceDisplaying = function(referenceId, referenceParentId)
{
    // Creates lines data array
    var lines = [].map.call(document.querySelectorAll('tr[data-reference]'), function (line) {
        return {
            reference: line.dataset.reference,
            deep: line.dataset.level,
            parent: line.dataset.referenceParent,
            checked: $('input[type="checkbox"]', $(line)).prop('checked'),
        };
    });

    // Sort by deep asc
    lines.sort(function (a, b) {
        return a.deep > b.deep;
    });

    var i, line;
    var map = {};
    for (i = 0; (line = lines[i]) !== undefined; i++) {
        if (line.checked) {
            map[line.reference] = line;

            if (undefined !== map[line.parent]) {
                delete map[line.parent];
            }
        }

        document.querySelector('tr[data-reference="' + line.reference + '"] .toggle').style.display = 'none';
    }

    var mapKeys = Object.keys(map);
    for (i = 0; (line = map[mapKeys[i]]) !== undefined; i++) {
        document.querySelector('tr[data-reference="' + line.reference + '"] .toggle').style.display = 'block';
    }
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
