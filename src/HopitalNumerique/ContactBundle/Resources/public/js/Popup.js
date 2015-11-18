/**
 * Gestion de la popup de contact.
 */
var Contact_Popup = function() {};


/**
 * Affiche la popup de contact.
 * 
 * @param json   adressesElectroniques JSON contenant les adresses des destinataires. Ex. : {"toto@anap.fr":"ANAP"}
 * @param string urlRedirection        URL de redirection
 * @param string objet                 (optionnel) Objet par défaut
 */
Contact_Popup.display = function(adressesElectroniques, urlRedirection, objet)
{
    if ($('#contact-popup').size() > 0)
    {
        $('#contact-popup').remove();
    }

    $.ajax({
        url: '/contact/popup',
        data: {
            destinataires: adressesElectroniques,
            urlRedirection: urlRedirection,
            objet: (undefined !== objet ? objet : '')
        },
        type: 'POST',
        dataType: 'html',
        success: function(data)
        {
            $('body').prepend(data);
            $('#contact-popup').modal({
                show: true,
                keyboard: false
            });
            $('#contact-popup form').validationEngine();
        }
    });
};
