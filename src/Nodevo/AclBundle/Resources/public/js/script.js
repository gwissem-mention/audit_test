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

    $('#divHeader-initiaux').width(($('#groupes-initiaux').width() - $('#firstcol-initiaux').width()) - 20);
    $('#table_div-initiaux').width(($('#groupes-initiaux').width() - $('#firstcol-initiaux').width()) - 20);

    $('#divHeader-non-initiaux').width(($('#groupes-non-initiaux').width() - $('#firstcol-non-initiaux').width()) - 20);
    $('#table_div-non-initiaux').width(($('#groupes-non-initiaux').width() - $('#firstcol-non-initiaux').width()) - 20);
    
});

//function to support scrolling of title and first column
fnScrollInitiaux = function(){
  $('#divHeader-initiaux').scrollLeft($('#table_div-initiaux').scrollLeft());
  $('#firstcol-initiaux').scrollTop($('#table_div-initiaux').scrollTop());
}

//function to support scrolling of title and first column
fnScrollNonInitiaux = function(){
  $('#divHeader-non-initiaux').scrollLeft($('#table_div-non-initiaux').scrollLeft());
  $('#firstcol-non-initiaux').scrollTop($('#table_div-non-initiaux').scrollTop());
}