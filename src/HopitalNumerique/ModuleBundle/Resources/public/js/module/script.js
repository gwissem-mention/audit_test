$(document).ready(function() { 

    //Gestion du bouton delete : changement du fichier uploadé
    $('.deleteUploadedFile').on('click',function(){
        $('.uploadedFile, .deleteUploadedFile ').hide();
        $('.uploadedFile').html('');
        $('.inputUpload').show();
        $('#hopitalnumerique_module_module_path').val('');
        $('#hopitalnumerique_module_module_file').val('');
    });
    $('form.toValidate').validationEngine();
	
});