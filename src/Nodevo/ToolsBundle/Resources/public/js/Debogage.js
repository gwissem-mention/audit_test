/**
 * Classes permettant d'aider à déboguer en JavaScript.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 * @version 20140228
 */
var Nodevo_Debogage = function() {};

/**
 * Raccourci vers Nodevo_Debogage.alert().
 */
function alerte(objet)
{
    Nodevo_Debogage.alert(objet);
}

/**
 * Affiche en détail dans une fenêtre le contenu d'un objet JavaScript.
 * 
 * @param objet L'objet à afficher
 * @return void
 */
Nodevo_Debogage.AFFICHE_PROPRIETES_VIDES = false;

/**
 * Affiche les propriétés d'un objet.
 * 
 * @param object objet L'objet à afficher.
 */
Nodevo_Debogage.alert = function(objet)
{
    var texteAlerte = 'Type : ' + Nodevo_Debogage._getTypeObjet(objet) + "\n\n";

    if (objet instanceof jQuery) {
        if (objet.length != undefined)
        {
            texteAlerte += 'Longueur du tableau : ' + objet.length + "\n\n";
            $.each(objet, function(indice, objetJquery) {
                texteAlerte += '----' + "\n\n" + 'Élément #' + (indice + 1) + ' (' + Nodevo_Debogage._getTypeObjet(objetJquery) + ')' + "\n\n";
                texteAlerte += Nodevo_Debogage._getChaineProprietesEtFonctions(objetJquery) + "\n\n";
            });
        }
        else texteAlerte += Nodevo_Debogage._getChaineProprietesEtFonctions(objet);
    } else if (objet instanceof Array) {
	texteAlerte += 'Longueur du tableau : ' + objet.length + "\n\n";
	for (var i in objet) {
	    texteAlerte += i + (Nodevo_Debogage.AFFICHE_PROPRIETES_VIDES ? '' : ' : ' + objet[i]) + "\n";
	}
    } else if ((typeof objet) == 'string' || (typeof objet) == 'number' || (typeof objet) == 'boolean') {
	texteAlerte += objet;
    } else {
	for (var i in objet) {
	    texteAlerte += i + (Nodevo_Debogage.AFFICHE_PROPRIETES_VIDES ? '' : ' : ' + objet[i]) + "\n";
	}
    }

    alert(texteAlerte);
};

/**
 * Retourne le type d'un objet.
 * 
 * @param object objet L'objet dont il faut retourner le type
 * @return string Le type de l'objet
 */
Nodevo_Debogage._getTypeObjet = function(objet)
{
    if ((typeof objet) == 'string')
        return 'Chaîne de caractères';
    if ((typeof objet) == 'number')
        return 'Nombre';
    if (objet instanceof Array) {
        return 'Tableau';
    }
    if (objet instanceof jQuery)
        return 'Objet jQuery';
    if (objet instanceof Element)
    {
        if (objet.tagName.toLowerCase() == 'input')
            return 'input[type=' + $(objet).attr('type').toLowerCase() + ']';
        return objet.tagName.toLowerCase();
    }
    switch (objet)
    {
        case 'object':
            return 'Objet';
        default:
            return (typeof objet);
    }
};

/**
 * Retourne la liste des fonctions de l'objet.
 * 
 * @param object objet L'objet dont il faut retourner les fonctions
 * @return string Les fonctions de l'objet
 */
Nodevo_Debogage._getChaineProprietesEtFonctions = function(objet)
{
    return 'PROPRIÉTÉS' + "\n\n" + Nodevo_Debogage._getChaineProprietes(objet) + "\n\n" + 'FONCTIONS' + "\n\n" + Nodevo_Debogage._getChaineFonctions(objet);
};

/**
 * Retourne la liste des propriétés de l'objet.
 * 
 * @param object objet L'objet dont il faut retourner les propriétés
 * @return string Les propriétés de l'objet
 */
Nodevo_Debogage._getChaineProprietes = function(objet)
{
    var proprietes = '';
        try
        {
            $.each(objet, function(propriete, valeur) {
                if (typeof valeur != 'function' && (Nodevo_Debogage.AFFICHE_PROPRIETES_VIDES || (valeur != '' && valeur != null)))
                    proprietes += propriete + ' : ' + valeur + "\n";
            });
        }
        catch (erreur)
        {
            proprietes += '### ERREUR ### ' + erreur + "\n";
        }
        return proprietes;
    };
    
/**
 * Retourne la liste des fonctions de l'objet.
 * 
 * @param object objet L'objet dont il faut retourner les fonctions
 * @return string Les fonctions de l'objet
 */
Nodevo_Debogage._getChaineFonctions = function(objet)
{
    var fonctions = '';
    try
    {
        $.each(objet, function(propriete, valeur) {
            if (typeof valeur == 'function')
                fonctions += propriete + '()' + "\n";
        });
    }
    catch (erreur)
    {
        fonctions += '### ERREUR ### ' + erreur + "\n";
    }
    return fonctions;
};
