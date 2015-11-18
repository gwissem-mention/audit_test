/**
 * Gestion de la popup de contact.
 */
var Contact_Popup = function() {};


/**
 * Affiche la popup de contact.
 * 
 * @param json   adressesElectroniques JSON contenant les adresses des destinataires avec en indice le nom. Ex. : {"ANAP":"toto@anap.fr"}
 * @param string urlRedirection        URL de redirection
 */
Contact_Popup.display = function(adressesElectroniques, urlRedirection)
{
    if ($('#contact-popup').size() > 0)
    {
        $('#contact-popup').remove();
    }

    $.ajax({
        url: '/contact/popup',
        data: {
            destinataires: adressesElectroniques,
            urlRedirection: urlRedirection
        },
        type: 'POST',
        dataType: 'html',
        success: function(data)
        {
            $('body').prepend(data);
            $('#contact-popup').modal('show');
        }
    });
};
