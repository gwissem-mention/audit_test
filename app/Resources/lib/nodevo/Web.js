/**
 * Classe permettant de gérer des fonctionnalités web (navigation, redirection...).
 * 
 * @author Rémi Leclerc
 */
var Nodevo_Web = function() {};

/**
 * Redirige l'internaute vers une autre page.
 * 
 * @param string url L'URL vers laquelle l'internaute sera redirigé
 * @return void
 */
Nodevo_Web.redirect = function(url)
{
    window.location = url;
};

/**
 * Recharge la page en cours.
 * 
 * @return void
 */
Nodevo_Web.reload = function()
{
    window.location.href = window.location.href;
};

/**
 * Redirige l'utilisateur vers la page précédente.
 * 
 * @return void
 */
Nodevo_Web.back = function()
{
    window.history.back();
};

/**
 * Imprime la page en cours.
 * 
 * @return void
 */
Nodevo_Web.print = function()
{
    window.print();
};
