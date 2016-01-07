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
    var loader = $('body').nodevoLoader();
    loader.start();
    
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
            $.fancybox({
                content: data,
                autoSize: false,
                autoHeight: true,
                width: 600,
                afterLoad:function() {
                    loader.finished();
                },
                afterShow:function() {
                    $('#contact-popup form').validationEngine({
                        promptPosition: 'bottomLeft'
                    });
                }
            });
        }
    });
};
