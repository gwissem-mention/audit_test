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
    $('#nodevouser_user_informationsmanquantes_organizationType, #nodevouser_user_informationsmanquantes_departement').change(function () {
        Hn_AccountBundle_InformationsManquantes.loadOrganizations();
    });
};

/**
 * Charge les départements.
 */
Hn_AccountBundle_InformationsManquantes.loadDepartements = function ()
{
    var departementId = $('#nodevouser_user_informationsmanquantes_departement').val();

    $.ajax({
        url: Routing.generate('hopital_numerique_user_counties'),
        data: {
            id: $('#nodevouser_user_informationsmanquantes_region').val(),
        },
        type: 'POST',
        success: function (data) {
            $('#nodevouser_user_informationsmanquantes_departement').html(data);
            $('#nodevouser_user_informationsmanquantes_departement').val(departementId);
            Hn_AccountBundle_InformationsManquantes.loadOrganizations();
        }
    });
};

/**
 * Charge les départements.
 */
Hn_AccountBundle_InformationsManquantes.loadOrganizations = function ()
{
    $.ajax({
        url: Routing.generate('hopital_numerique_user_front_etablissements_informationsPersonelles'),
        data: {
            idDepartement: $('#nodevouser_user_informationsmanquantes_departement').val(),
            idTypeEtablissement: $('#nodevouser_user_informationsmanquantes_organizationType').val()
        },
        type: 'POST',
        success: function (data) {
            var value = $('#nodevouser_user_informationsmanquantes_organization').val();
            $('#nodevouser_user_informationsmanquantes_organization').html( data );
            $('#nodevouser_user_informationsmanquantes_organization option[value="' + value + '"]').prop('selected', true);
        }
    });
};
