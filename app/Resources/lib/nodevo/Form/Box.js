/**
 * Classe de gestion des boutons de type radio et checkbox.
 * 
 * @author Rémi Leclerc
 */
var Nodevo_Form_Box = function() {};

/**
 * Coche une case.
 * 
 * @param Element field Case à cocher
 */
Nodevo_Form_Box.check = function(field)
{
    $(field).prop('checked', true);
};

/**
 * Décoche une case.
 * 
 * @param Element field Case à cocher
 */
Nodevo_Form_Box.uncheck = function(field)
{
    $(field).prop('checked', false);
};

/**
 * Coche une case si décochée ou la décoche si cochée.
 * 
 * @param Element field Case à cocher
 */
Nodevo_Form_Box.toggleCheck = function(field)
{
    $(field).prop('checked', !$(field).is(':checked'));
};

/**
 * Retourne tous les boutons de type radio/checkbox cochés dans un élément.
 * 
 * @param Element parent Élément qui contient les boutons
 */
Nodevo_Form_Box.getCheckedBoxes = function(parent)
{
    return $(parent).find('input[type=radio]:checked, input[type=checkbox]:checked');
};

/**
 * Retourne le nombre de boutons de type radio/checkbox cochés dans un élément.
 * 
 * @param Element parent Élément qui contient les boutons
 */
Nodevo_Form_Box.countCheckedBoxes = function(parent)
{
    return Nodevo_Form_Box.getCheckedBoxes(parent).size();
};

/**
 * Retourne si l'élément contient au moins un boutons de type radio/checkbox coché.
 * 
 * @param Element parent Élément qui contient les boutons
 */
Nodevo_Form_Box.hasCheckedBox = function(parent)
{
    return Nodevo_Form_Box.countCheckedBoxes(parent) > 0;
};

/**
 * Coche toutes les cases.
 * 
 * @param Element container Container
 */
Nodevo_Form_Box.checkAll = function(container)
{
    $(container).find('input[type=radio], input[type=checkbox]').prop('checked', true);
};

/**
 * Décoche toutes les cases.
 * 
 * @param Element container Container
 */
Nodevo_Form_Box.uncheckAll = function(container)
{
    $(container).find('input[type=radio]:checked, input[type=checkbox]:checked').prop('checked', false);
};
