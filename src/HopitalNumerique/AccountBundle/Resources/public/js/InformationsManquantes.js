/**
 * Gestion des informations manquantes.
 */
var Hn_AccountBundle_InformationsManquantes = function () {};

/**
 * Initialisation.
 */
Hn_AccountBundle_InformationsManquantes.init = function ()
{
    $('form.toValidate').validationEngine();
    Hn_AccountBundle_InformationsManquantes.initEvents();

    Hn_AccountBundle_InformationsManquantes.loadDepartements();
};

/**
 * Initialisation des événements.
 */
Hn_AccountBundle_InformationsManquantes.initEvents = function ()
{
    $('#nodevouser_user_informationsmanquantes_region').change(function () {
        Hn_AccountBundle_InformationsManquantes.loadDepartements();
    });
    $('#nodevouser_user_informationsmanquantes_departement').change(function () {
        Hn_AccountBundle_InformationsManquantes.loadEtablissementRattachementSantes();
    });
};

/**
 * Charge les départements.
 */
Hn_AccountBundle_InformationsManquantes.loadDepartements = function ()
{
    var departementId = $('#nodevouser_user_informationsmanquantes_departement').val();

    $.ajax({
        url: Routing.generate('hopital_numerique_user_front_departements_informationsPersonelles'),
        data: {
            id: $('#nodevouser_user_informationsmanquantes_region').val(),
        },
        type: 'POST',
        success: function (data) {
            $('#nodevouser_user_informationsmanquantes_departement').html(data);
            $('#nodevouser_user_informationsmanquantes_departement').val(departementId);
            Hn_AccountBundle_InformationsManquantes.loadEtablissementRattachementSantes();
        }
    });
};

/**
 * Charge les départements.
 */
Hn_AccountBundle_InformationsManquantes.loadEtablissementRattachementSantes = function ()
{
    $.ajax({
        url: Routing.generate('hopital_numerique_user_front_etablissements_informationsPersonelles'),
        data: {
            idDepartement: $('#nodevouser_user_informationsmanquantes_departement').val(),
            idTypeEtablissement: $('#nodevouser_user_informationsmanquantes_statutEtablissementSante').val()
        },
        type: 'POST',
        success: function (data) {
            $('#nodevouser_user_informationsmanquantes_etablissementRattachementSante').html(data);
        }
    });
};
