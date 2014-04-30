$(document).ready(function() {
    if( $('.addChapitre').length > 0 ){
        //Ajoute un chapitre
        $('.addChapitre').click(function(){
            $.ajax({
                url  : $('#add-chapitre-url').val(),
                data : {
                    key : $('#outil-id').val()
                },
                type     : 'POST',
                success  : function( data ){
                    if( data != '' )
                        $('#chapitres ol:first').append( data );
                    else
                        apprise('Une erreur est survenue lors de l\'ajout de votre chapitre, merci de réessayer');
                }
            });
        });
    }

    //bind de Validation Engine
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();
});

//Toogle Block and manage classes
function toggle( block )
{
    $('.'+block).slideToggle();

    if ( $('.'+block).is(':visible') ){
        $('.'+block).find('input').addClass('validate[required,maxSize[255]]');
        $('.'+block).find('select').addClass('validate[required]');
    }else{
        $('.'+block).find('input').removeClass('validate[required,maxSize[255]]');
        $('.'+block).find('select').removeClass('validate[required]');
    }   
}