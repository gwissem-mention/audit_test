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
            if ( $('.grid_massactions select option:selected').text() == 'Supprimer' ) {
                apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
                    if(r) { 
                        $('.grid form').submit();
                    }
                });
            }else
                $('.grid form').submit();
        }
    });
});

/**
 * Initialise le champ date d'u filtre.
 * 
 * @return void
 */
function NodevoGridBundle_Grid_initFiltreDate(gridHash, columnId)
{
    $('#' + gridHash + '__' + columnId + '__query__from').attr('type', 'hidden');
    $('#' + gridHash + '__' + columnId + '__query__to').attr('type', 'hidden');
    var dateFrom = $('#' + gridHash + '__' + columnId + '__query__from').val();
    var dateTo = $('#' + gridHash + '__' + columnId + '__query__to').val();
    if (dateFrom.length == 10)
        $('#alt' + gridHash + '__' + columnId + '__query__from').val(dateFrom.substr(8, 2) + '/' + dateFrom.substr(5, 2) + '/' + dateFrom.substr(0, 4));
    if (dateTo.length == 10)
        $('#alt' + gridHash + '__' + columnId + '__query__to').val(dateTo.substr(8, 2) + '/' + dateTo.substr(5, 2) + '/' + dateTo.substr(0, 4));
    $('#alt' + gridHash + '__' + columnId + '__query__from').datepicker({ altFormat:'yy-mm-dd', altField:'#' + gridHash + '__' + columnId + '__query__from' });
    $('#alt' + gridHash + '__' + columnId + '__query__to').datepicker({ altFormat:'yy-mm-dd', altField:'#' + gridHash + '__' + columnId + '__query__to' });
}