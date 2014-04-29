$(document).ready(function() {
    //bind de Validation Engine
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