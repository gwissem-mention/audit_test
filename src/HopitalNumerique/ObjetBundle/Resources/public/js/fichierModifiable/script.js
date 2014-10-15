$(document).ready(function() { 
    //Gestion du bouton delete : changement du fichier uploadé
    $('.deleteUploadedFile').on('click',function(){
        $('.uploadedFile, .deleteUploadedFile ').hide();
        $('.uploadedFile').html('');
        $('.inputUpload').show();
        $('#hopitalnumerique_objet_fichiermodifiable_pathEdit').val('');
        $('#hopitalnumerique_objet_fichiermodifiable_fileEdit').val('');
    });
    
    $('form.toValidate').validationEngine();
});