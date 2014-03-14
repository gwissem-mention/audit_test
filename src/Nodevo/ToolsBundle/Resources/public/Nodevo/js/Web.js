/**
 * Classes permettant de gérer des fonctionnalités Web (navigation, redirection...).
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
var Nodevo_Web = function() {};

/**
 * Redirige l'internaute vers une autre page.
 * 
 * @param string url L'URL vers laquelle l'internaute sera redirigé
 * @return void
 */
Nodevo_Web.redirige = function(url)
{
    window.location = url;
};

/**
 * Recharge la page en cours.
 * 
 * @return void
 */
Nodevo_Web.rechargePage = function()
{
    window.location.href = window.location.href;
};

/**
 * Redirige l'utilisateur vers la page précédente.
 * 
 * @return void
 */
Nodevo_Web.dirigeVersPagePrecedente = function()
{
    window.history.back();
};