var loader;

$(document).ready(function() {
    new AjaxList($('.ajax-list-select2'));
    new CountyList($('#nodevo_user_user_region'), $('#nodevo_user_user_county'));
    new HobbyCollection();

    $('#nodevo_user_user_phoneNumber, #nodevo_user_user_cellPhoneNumber').focus(function(){
        if( $(this).value() === "" ){
            $(this).value("");
        } else {
            $(this).select();
        }
    });

    $('#nodevo_user_user_activities').select2({width: '100%'});

    loader = $('#form_edit_user').nodevoLoader();

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

    //Chargement des masks du formulaire
    chargementMaskFormulaire();

    //bind de Validation Engine
    $('form.toValidate').validationEngine();
});

/**
 * Chargement des différents mask sur les inputs
 */
function chargementMaskFormulaire()
{
    //Mask
    $("#nodevo_user_user_cellPhoneNumber").mask($("#nodevo_user_user_cellPhoneNumber").data("mask"));
    $("#nodevo_user_user_phoneNumber").mask($("#nodevo_user_user_phoneNumber").data("mask"));
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
