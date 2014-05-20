//Permet de cacher les filtres
$( ".grid-filters-title" ).click(function() {
    $( ".grid-filters-content" ).slideToggle( "slow" );
});

$(function () {
    //Gestion des actions de masse
    $('.grid_massactions .submit').click(function() {
        //Si on selection une action
        if( $('.grid_massactions select').val() != -1 ){

            //on vérifie de quelle action il s'agit, si c'est l'action delete, on demande une confirmation
            if ( $('.grid_massactions select option:selected').text() == 'Refuser inscription' )
            {
                apprise('Quelle est la raison du refus ?', {'input':true}, function(r)
                {
                    $.cookie('textMailInscription', r);
                    $('.grid form').submit();
                });
            }else
                $('.grid form').submit();
        }
    });
});