jQuery(document).ready(function() {
    //Evènement appeller au changement d'un acl
    $('.nodevo-acls .btn').click(function() {
        var loader = $(this).parent().nodevoLoader().start();
        var type   = $(this).data('type');
        that = $(this);

        //appel AJAX qui set la valeur en base et met à jour l'état visuel du bouton
        $.ajax({
            url  : $('#acl-url').val(),
            data : {
                type      : type,
                ressource : $(this).data('ressource'),
                role      : $(this).data('role'),
            },
            dataType : 'json',
            type     : 'POST',
            success  : function( data ){
                //cas écriture
                if( type != 1 && data.class == "btn-success" )
                    that.siblings().removeClass( "btn-default btn-success" ).addClass(data.class);
                //cas lecture
                if( type == 1 && data.class == "btn-default" )
                    that.siblings().removeClass( "btn-default btn-success" ).addClass(data.class);

                that.removeClass( "btn-default btn-success" ).addClass(data.class);
                loader.finished();
            }
        });
    });
});

//function to support scrolling of title and first column
fnScroll = function(){
  $('#divHeader').scrollLeft($('#table_div').scrollLeft());
  $('#firstcol').scrollTop($('#table_div').scrollTop());
}