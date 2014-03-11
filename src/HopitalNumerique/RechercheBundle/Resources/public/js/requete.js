$(document).ready(function() {
    //gestion du click pour mettre la requête par défaut
    $('.default:not(.active)').click(function(){
        link = $(this).data('link');

        $.ajax({
            url      : link,
            type     : 'POST',
            dataType : 'json',
            success  : function( data ){
                window.location = data.url;
            }
        });
    });
});

/**
 * Reprise de la fonction ADMIN:deletewithConfirm
 */
function deleteWithConfirm(path)
{
    apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if(r) { 
            $.ajax({
                url      : path,
                type     : 'POST',
                dataType : 'json',
                success : function( data ){
                    window.location = data.url;
                }
            });
        }
    });
}

/**
 * Edit en AJAX de la requete
 */
function editRequete( path )
{
    apprise('Nouveau nom de la requête', {'input':true,'textOk':'Mettre à jour','textCancel':'Annuler'}, function(r) {
        if( r ){
            $.ajax({
                url  : path,
                data : {
                    nom : r
                },
                type     : 'POST',
                dataType : 'json',
                success  : function( data ){
                    window.location = data.url;
                }
            });
        }
    });
}