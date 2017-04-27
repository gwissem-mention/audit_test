var loader;

$(document).ready(function() {
    $('#nodevo_user_user_telephoneDirect, #nodevo_user_user_telephonePortable').focus(function(){
        if( $(this).value() === "" ){
            $(this).value("");
        } else {
            $(this).select();
        }
    });

    $('#nodevo_user_user_typeActivite').select2({width: '100%'});

    loader = $('#form_edit_user').nodevoLoader();
    var idDepartement = 0;

    // ------- Gestion de la photo de profil --------
    //Gestion du bouton delete : changement du fichier uploadé
    $('.deleteUploadedFile').on('click',function(){
        $('.uploadedFile, .deleteUploadedFile ').hide();
        $('.uploadedFile').html('');
        $('.inputUpload').show();
        $('#nodevo_user_user_path').val('');
        $('#nodevo_user_user_file').val('');
    });

    // ------- Gestion des listes déroulantes en AJAX ----------

    //Récupération de l'id du département si il on est en édition
    if(null !== $('#nodevo_user_user_departement').val())
        idDepartement = $('#nodevo_user_user_departement').val();

    // --- Département

    //Chargement des départements du formulaire en fonction de la région selectionnée
    chargementDepartement();

    //Si le département était renseigné on le recharge une fois que la liste des département est correct
    if( 0 != idDepartement )
        $('#nodevo_user_user_departement').val(idDepartement);

    //Ajout de la fonction de chargement des départements sur le on change des régions
    $('#nodevo_user_user_region').on('change', function()
    {
        chargementDepartement();
    });

    //Chargement des masks du formulaire
    chargementMaskFormulaire();

    //Ouerture de l'onglet ayant des données : si l'un des champs de structure n'est pas vide on ouvre l'onglet
    if('' != $('#nodevo_user_user_nomStructure').val()
        || '' != $('#nodevo_user_user_fonctionStructure').val())
    {
        $('#autre_etablissement_sante_collapse').click();
    }

    //bind de Validation Engine
    $('form.toValidate').validationEngine();

    $(function() {
        var $select = $('.ajax-select2-list');
        $select.select2({
            ajax: {
                url: $select.data('url'),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            minimumInputLength: 3,
            width: '100%'
        });
    });
});

/**
 * Permet de charger les départements en fonction de la région selectionné en ajax
 */
function chargementDepartement(){
    loader.start();

    $.ajax({
        url  : $('#departement-url').val(),
        data : {
            id : $('#nodevo_user_user_region').val(),
        },
        type    : 'POST',
        success : function( data ){
            var value = $('#nodevo_user_user_departement').val();
            $('#nodevo_user_user_departement').html( data );
            $('#nodevo_user_user_departement option[value="' + value + '"]').prop('selected', true);
            loader.finished();
        }
    });
}

/**
 * Chargement des différents mask sur les inputs
 */
function chargementMaskFormulaire()
{
    //Mask
    $("#nodevo_user_user_telephonePortable").mask($("#nodevo_user_user_telephonePortable").data("mask"));
    $("#nodevo_user_user_telephoneDirect").mask($("#nodevo_user_user_telephoneDirect").data("mask"));
}

/**
 * Fonction appelée lors du click sur le bouton save
 */
function sauvegardeFormulaire()
{
    //Récupération de tout les champs dans établissement de santé
    var childsEtablissementSante = $("#etablissement_sante input,#etablissement_sante select");

    //Si l'onglet est caché, on vide tous ses inputs
    if('collapsed' == $("#etablissement_sante_collapse").attr('class'))
    {
        childsEtablissementSante.each(function(){
            $(this).val('');
        })
    }

    //Récupération de tout les champs dans établissement de santé
    var childsAutreEtablissementSante = $("#autre_etablissement_sante input,#autre_etablissement_sante select");

    if('collapsed' == $("#autre_etablissement_sante_collapse").attr('class'))
    {
        childsAutreEtablissementSante.each(function(){
            $(this).val('');
        })
    }

    $('form').submit();
}
