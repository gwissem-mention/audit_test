$(document).ready(function() { 
    //Gestion du bouton delete : changement du fichier uploadé
    $('.deleteUploadedFile').on('click',function(){
        $('.uploadedFile, .deleteUploadedFile ').hide();
        $('.uploadedFile').html('');
        $('.inputUpload').show();
        $('#hopitalnumerique_domaine_domaine_path').val('');
        $('#hopitalnumerique_domaine_domaine_file').val('');
    });

    $('form.toValidate').validationEngine();
});