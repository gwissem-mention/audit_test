$(document).ready(function() {
    if( $('.addChapitre').length > 0 ){
        //Ajoute un chapitre
        $('.addChapitre').click(function(){
            $.ajax({
                url  : $('#add-chapitre-url').val(),
                data : {
                    key : $('#outil-id').val()
                },
                type     : 'POST',
                success  : function( data ){
                    if( data != '' ){
                        $('#chapitres ol:first').append( data );

                        if( $('#chapitres ol li').length > 0){
                            $('.designForBlank').hide();
                        }

                    }else
                        apprise('Une erreur est survenue lors de l\'ajout de votre chapitre, merci de réessayer');
                }
            });
        });
    }

    //Création et gestion de l'arborescence des chapitres
    $('#chapitres').nestable({'maxDepth':2,'group':0}).on('change', function() {
        var serializedDatas = $(this).nestable('serialize');

        $.ajax({
            url  : $('#order-chapitre-url').val(),
            data : {
                datas : serializedDatas
            },
            type     : 'POST',
            dataType : 'json',
            success  : function( data ){
                //console.log( 'reorder executed' );
            }
        });
    });

    //Fancybox
    $('.fancy').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'height'    : '600px',
        'scrolling' : 'no',
        'modal'     : true
    });

    //bind de Validation Engine
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();
});

//Toogle Block and manage classes
function toggle( block )
{
    $('.'+block).slideToggle();

    if ( $('.'+block).is(':visible') ){
        $('.'+block).find('input').addClass('validate[required,maxSize[255]]');
        $('.'+block).find('select').addClass('validate[required]');
    }else{
        $('.'+block).find('input').removeClass('validate[required,maxSize[255]]');
        $('.'+block).find('select').removeClass('validate[required]');
    }   
}

//Supprime le contenu en cours de visualisation
function deleteChapitre( id, url )
{
    apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if(r) { 
            $.ajax({
                url  : url,
                data : {
                    id : id
                },
                type     : 'POST',
                dataType : 'json',
                success  : function( data ){
                    if( data.success ){
                        //correction effectuée : si on a supprimer tous les enfants alors on enlève le petit '-' du parent
                        if( data.childs == 0 ){
                            $('#chapitres #chapitre-' + id).parent().parent().find('button').each(function(){
                                $(this).remove();
                            })
                        }

                        //supprime l'élément dans le HTML
                        $('#chapitres #chapitre-' + id).remove();

                        if( $('#chapitres ol li').length == 0)
                            $('.designForBlank').show();
                    }
                }
            });
        }
    });
}

//sauvegarde le chapitre
function saveChapitre( url )
{
    $.ajax({
        url      : url,
        data     : $('#fancybox form').serialize(),
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            if( data.success ) {
                $('#chapitres #chapitre-' + data.id + ' > .dd3-content a').html( data.titre );
                $.fancybox.close(true);
            }
        }
    });
}

//Supprime le contenu en cours de visualisation
function deleteQuestion( id, url )
{
    apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if(r) { 
            $.ajax({
                url  : url,
                data : {
                    id : id
                },
                type     : 'POST',
                dataType : 'json',
                success  : function( data ){
                    if( data.success ){
                        //correction effectuée : si on a supprimer tous les enfants alors on enlève le petit '-' du parent
                        if( data.childs == 0 ){
                            $('#questions #question-' + id).parent().parent().find('button').each(function(){
                                $(this).remove();
                            })
                        }

                        //supprime l'élément dans le HTML
                        $('#questions #question-' + id).remove();

                        if( $('#questions ol li').length == 0)
                            $('#questions .designForBlank').show();
                    }
                }
            });
        }
    });
}

//Selectionne un chapitre et charge l'ensemble des questions liés
function selectChapter( id, url )
{
    //collapse Design For Blank
    if( $('#questions .chapitre').val() == 0 )
        $('#questions .selectChapitre').hide();

    $.ajax({
        url     : url,
        type    : 'POST',
        success : function( data ){
            $('#questions .results').html( data );
        }
    });

    $('#questions .chapitre').val( id );
}

//sauvegarde le chapitre
function saveQuestion( url )
{
    $.ajax({
        url      : url,
        data     : $('#fancybox form').serialize(),
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            if( data.success ) {
                console.log( data );
                //$('#questions #chapitre-' + data.id + ' > .dd3-content a').html( data.titre );
                $.fancybox.close(true);
            }
        }
    });
}