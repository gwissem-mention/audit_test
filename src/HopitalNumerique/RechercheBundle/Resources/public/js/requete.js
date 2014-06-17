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

    width = $( window ).width();
    if( width < 991 ) {
        $('.ligne').on('click', function(){
            window.location = $(this).data('target');
        });
    }
});

/**
 * Reprise de la fonction ADMIN:deletewithConfirm
 */
function deleteWithConfirm( path )
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

/**
 * Affiche les détail de la requete enregistrée
 */
function showDetails( path )
{
    $.fancybox.open( path , {
        type     : "ajax",
        autoSize : false,
        width    : 800,
        padding  : 0,
        height   : 'auto'
    });
}

/**
 * Prend en compte la requete par défaut ou la requete active
 */
function handleRefs()
{
    refs = $.parseJSON( $('#requete-refs').val() );

    $.each(refs, function(key, val){
        $.each(val.reverse(), function( key, item ){
            element = $('.arbo-requete .element-'+item);
            if( $(element).hasClass('hide') ){
                //affiche l'élément
                showElementRecursive( $(element) );

                //si c'est un parent, on show ces enfants (NON recursif)
                $(element).find('li.hide').removeClass('hide');
            }
        });
    });
}

/**
 * Affiche l'élément en mode récursif
 */
function showElementRecursive( destItem )
{
    $(destItem).removeClass('hide');

    if ( $(destItem).parent().parent().hasClass('hide') )
        showElementRecursive( $(destItem).parent().parent() );
}

/**
 * Sauvegarde la popin de mail requete
 */
function getNotifiedRequete(path)
{
    $.ajax({
        url  : path,
        data : {
            dateDebut : $('#dateDebut').val() != '' ? $('#dateDebut').datepicker("getDate").getTime() / 1000 : null,
            dateFin   : $('#dateFin').val()   != '' ? $('#dateFin').datepicker("getDate").getTime() / 1000   : null,
            notified  : $('#toggle').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            window.location = data.url;
        }
    });
}