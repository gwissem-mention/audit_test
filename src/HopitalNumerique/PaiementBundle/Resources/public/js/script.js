$(document).ready(function() {
    $('.checkbox').on('click', function(){
        total = 0;
        $('.paiements .checkbox').each(function(){
            if( $(this).prop('checked') === true ){
                total = total + parseInt($(this).val());
            }
        });

        $('.paiements .total').html( total );
    });
});