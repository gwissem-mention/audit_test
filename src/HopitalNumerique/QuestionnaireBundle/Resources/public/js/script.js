$(document).ready(function() {
    
    $('input.question-type-date').datepicker({
        dateFormat:'yy-mm-dd'
    });
    
	//gestion du bouton delete : changement du fichier uploadé
    $('.deleteUploadedFile').on('click',function(){
        $(this).hide();
        $(this).parent().find('.uploadedFile').hide();
        $(this).parent().find('.inputUpload').show();
        $('#' + $(this).data('path') ) .val('');
    });
});