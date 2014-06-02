$(document).ready(function() {
    
    $('input.question-type-date').datepicker({
        dateFormat:'yy-mm-dd'
    });

    //Select2
    $('input.select2-multiple-entity').select2({
        placeholder: "Choisissez un ou plusieurs choix",
        allowClear: true,
        formatNoMatches : function(){
            return "Aucun résultat trouvé.";
        }
    });
    
	//gestion du bouton delete : changement du fichier uploadé
    $('.deleteUploadedFile').on('click',function(){
        $(this).hide();
        $(this).parent().find('.uploadedFile').hide();
        $(this).parent().find('.inputUpload').show();
        $('#' + $(this).data('path') ) .val('');
    });
});