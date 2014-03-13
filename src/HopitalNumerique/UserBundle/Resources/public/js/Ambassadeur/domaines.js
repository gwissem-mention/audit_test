function saveDomaines()
{
    var domaines = [];

    $('#domaines tbody tr input').each(function(){
        if( $(this).prop('checked') )
            domaines.push( $(this).data('id') )
    });

    console.log( domaines );
}