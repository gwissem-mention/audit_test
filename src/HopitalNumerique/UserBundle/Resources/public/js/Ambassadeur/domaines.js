function saveDomaines()
{
    var domaines = new Array();

    $('#domaines tbody tr select').each(function(){
        if( $(this).val() !== "" )
        {
            domaines[$(this).data('id')] = $(this).val();
        }
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
