/**
 * Gestion du grid Nodevo.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var NodevoGridBundle_Grid = function() {};


$(document).ready(function() {
    NodevoGridBundle_Grid.init();
});


/**
 * Initialise le fonctionnement du grid.
 * 
 * @return void
 */
NodevoGridBundle_Grid.init = function()
{
    NodevoGridBundle_Grid.initEvenements();
};



/**
 * Initialise les événements du grid.
 * 
 * @return void
 */
NodevoGridBundle_Grid.initEvenements = function()
{
    NodevoGridBundle_Grid.initFiltresLabel_click();
};


/**
 * Initialise l'événement du clic sur le label des filtres pour afficher ces derniers.
 * 
 * @return void
 */
NodevoGridBundle_Grid.initFiltresLabel_click = function()
{
    $('.grid-search .panel-collapse').click(function() {
        NodevoGridBundle_Grid.toggleAffichageFiltre(this);
    });
};
/**
 * Initialise l'événement du clic sur le label des filtres pour afficher ces derniers.
 * 
 * @return void
 */
NodevoGridBundle_Grid.toggleAffichageFiltre = function(elementFiltrePanel)
{
    $(elementFiltrePanel).find('a').children().toggleClass("fa-chevron-down fa-chevron-up");
    $(elementFiltrePanel).closest(".panel-heading").next().slideToggle({duration: 200});
    $(elementFiltrePanel).closest(".panel-heading").toggleClass('rounded-bottom');
};