function saveDomaines()
{
    var domaines = [];

    $('#domaines tbody tr input').each(function(){
        if( $(this).prop('checked') )
            domaines.push( $(this).data('id') )
    });

    $.ajax({
        url  : $('#domaines-save-url').val(),
        data : {
            domaines : domaines,
            id       : $('#user_id').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            window.location = data.url;
        }
    });
}