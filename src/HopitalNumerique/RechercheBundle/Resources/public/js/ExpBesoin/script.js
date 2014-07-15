$(document).ready(function() {
    if( $('.addQuestion').length > 0 )
    {
        //Ajoute un chapitre
        $('.addQuestion').click(function(){
            apprise('Titre de la question', {'input' : true, 'textOk' : 'Ajouter', 'textCancel' : 'Annuler'}, function(r) {
                if(r) { 
                    addQuestion( r );
                }
            });
        });
    }

    //Création et gestion de l'arborescence des chapitres
    if( $('#questions').length > 0 ){
        $('#questions').nestable({'maxDepth':1,'group':0}).on('change', function() {
            var serializedDatas = $(this).nestable('serialize');

            $.ajax({
                url  : $('#order-question-url').val(),
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
    }

    //Fancybox
    if( $('.fancy').length > 0 )
        initFancyBox();
    
    //bind de Validation Engine
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();
});

// Initialise la fancybox
function initFancyBox()
{
    $('.fancy').fancybox({
            'padding'   : 0,
            'autoSize'  : false,
            'width'     : '80%',
            'height'    : '600px',
            'scrolling' : 'no',
            'modal'     : true
        });
}

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

//ajoute une question
function addQuestion( titre )
{
    $.ajax({
        url  : $('#add-question-url').val(),
        data : {
            titre : titre
        },
        type     : 'POST',
        success  : function( data ){
            if( data != '' ){
                $('#questions ol:first').append( data );

                if( $('#questions ol li').length > 0){
                    $('.designForBlank').hide();
                }

                //Forcer le click sur la question ajoutée

            }else
                apprise('Une erreur est survenue lors de l\'ajout de votre question, merci de réessayer.');
        }
    });
}

//Editer une question
function editQuestion( id, url)
{ 
    apprise('Titre de la question', {'input' : true, 'textOk' : 'Modifier', 'textCancel' : 'Annuler'}, function(rep) {
        if(rep)
        { 
            $.ajax({
                url  : url,
                data : {
                    id : id,
                    titre : rep
                },
                type     : 'POST',
                success  : function( data ){
                    if( data != '' )
                    {
                        location.reload();
                        //Forcer le click sur la question éditée
                        
                        // $.ajax({
                        //     url: "",
                        //     context: document.body,
                        //     success: function(s,x){
                        //         $('#questions').html(s);
                        //     }
                        // });
                    }
                    else
                    {
                        apprise('Une erreur est survenue lors de l\'ajout de votre question, merci de réessayer.');
                    }
                }
            });
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
                            $('#questions #chapitre-' + id).parent().parent().find('button').each(function(){
                                $(this).remove();
                            })
                        }

                        location.reload();
                    }
                }
            });
        }
    });
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
            if( data.success ){
                $.fancybox.close(true);
                window.location.reload();
            }
        }
    });
}

//Supprime le contenu en cours de visualisation
function deleteReponse( id, url )
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
function selectQuestion( id, url )
{
    $('#reponses .selectionQuestion').hide();
    
    //bind de Validation Engine
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();

    $.ajax({
        url     : url,
        type    : 'POST',
        success : function( data ){
            $('#reponses .results').html( data );
        }
    });

    $('#reponses .question').val( id );
}

//Selectionne un chapitre et charge l'ensemble des questions liés
function addReponse( url )
{
    if ( $('#designForForm form').validationEngine('validate') ) {
        $.ajax({
            url     : url,
            data    :  $('#designForForm form').serialize(),
            type    : 'POST',
            success : function( data ){
                //Ajout de la réponse
                $('#reponses-dd ol:first').append( data );
                //window.location.reload();
            }
        });
    }
}

//Met à jour le nombre d'enfants sélectionés dans la popin
function updateNbChilds()
{
    $('#references-tab .ref').each(function(){
        childs         = $(this).data('childs');
        parentLevel    = $(this).data('level');
        nbChecked      = 0;
        nbChildsDirect = 0;
        
        if( childs.length > 0 ) {
            $.each(childs,function(key, val){
                if ( $('.ref-'+val+' .checkbox').prop('checked') && $('.ref-'+val).data('level') == parentLevel + 1 )
                    nbChecked++
                
                if ( $('.ref-'+val).data('level') == parentLevel + 1 )
                    nbChildsDirect++
            });
        }

        $(this).find('.nbChilds').html( nbChecked );
        $(this).find('.nbChildsDirect').html( nbChildsDirect );
    })
}

//Gère le collapse dans la pop-in des références
function manageCollapse(element, way)
{
    childs = $(element).parent().parent().data('childs');
    level  = $(element).parent().parent().data('level') + 1;

    $.each(childs,function(key, val){
        if( way === 'collapse' )
            $('.ref-'+val).slideUp();
        else{
            if ( $('.ref-'+val).data('level') == level){
                $('.ref-'+val).slideDown();
                $('.ref-'+val+' .btn i').removeClass('fa-arrow-down').addClass('fa-arrow-right');
            }
        }
    });

    $(element).find('i').toggleClass('fa-arrow-down fa-arrow-right');
}

function saveReponse(idReponse, url)
{
    var idQuestion      = $('#expBesoin-reponse-' + idReponse).val();
    var isAutreQuestion = $('#toggle-reponses-' + idReponse).val();

    $.ajax({
        url  : url,
        data : {
            idReponse : idReponse,
            isAutreQuestion : isAutreQuestion,
            idQuestion : idQuestion
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            apprise('Reponse modifiée.');
        }
    });
}

//Sauvegarde les références de l'objet et du contenu
function saveReferences( expBesoin, reponse )
{
    var references = [];
    var loader     = $('.panel-body').nodevoLoader().start();

    $('#references-tab .ref').each(function() {
        //si la référence est cochée, on l'ajoute dans les références à linker
        if ( $(this).find('.checkbox').prop('checked') ) {
            var ref = {};

            //ref id
            ref.id = $(this).data('id');

            //type primary
            ref.type = $(this).find('.toggle-slide').hasClass('active');

            references.push( ref );    
        }
    });

    //JSONify IT !
    json = JSON.stringify( references );

    //save the value
    $.ajax({
        url  : $('#save-references-url').val(),
        data : {
            references : json
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            alert(reponse);
            alert(data.note);
            $('#reponses-dd #reponse-'+reponse+' > .dd3-content .text-muted span').html( data.note );

            loader.finished();
            $.fancybox.close(true);
        }
    });

    saveReponse($('#expbesoinreponse-id').val(), $('#save-reponse-url').val());
}