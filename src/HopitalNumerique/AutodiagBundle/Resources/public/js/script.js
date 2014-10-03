$(document).ready(function() {
    if( $('.addChapitre').length > 0 ){
        //Ajoute un chapitre
        $('.addChapitre').click(function(){
            apprise('Titre du chapitre', {'input' : true, 'textOk' : 'Ajouter', 'textCancel' : 'Annuler'}, function(r) {
                if(r) { 
                    addChapitre( r );
                }
            });
        });
    }

    //Création et gestion de l'arborescence des chapitres
    if( $('#chapitres').length > 0 ){
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
            'height'    : '650px',
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

//toggle le type de la question et cache les champs inutiles
function toggleTypeQuestion()
{
    if( $('#hopitalnumerique_autodiag_question_type').val() == 417 ){
        $('.txt').show();
        $('.not-txt').hide();
        $('#hopitalnumerique_autodiag_question_options').removeClass('validate[required]');
    }else if ($('#hopitalnumerique_autodiag_question_type').val() == 415 || $('#hopitalnumerique_autodiag_question_type').val() == 416 ){
        $('.txt').hide();
        $('.not-txt').show();
        $('#hopitalnumerique_autodiag_question_options').addClass('validate[required]');
    }else{
        $('.txt').hide();
        $('.not-txt').hide();
        $('#hopitalnumerique_autodiag_question_options').removeClass('validate[required]');
    }
}

//ajoute un chapitre
function addChapitre( titre )
{
    $.ajax({
        url  : $('#add-chapitre-url').val(),
        data : {
            key   : $('#outil-id').val(),
            titre : titre
        },
        type     : 'POST',
        success  : function( data ){
            if( data != '' ){
                $('#chapitres ol:first').append( data );

                if( $('#chapitres ol li').length > 0){
                    $('.designForBlank').hide();
                }

                initFancyBox();
                apprise('Chapitre ajouté');
            }else
                apprise('Une erreur est survenue lors de l\'ajout de votre chapitre, merci de réessayer');
        }
    });
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

                        //clean questions
                        $('#questions .chapitre').val( 0 );
                        $('#questions .selectChapitre').show();
                        $('#questions .results').html('');
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
            if( data.success ){
                $.fancybox.close(true);
                window.location.reload();
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
    if( $("#fancybox form").validationEngine('validate') ) {
       $.ajax({
            url     : url,
            data    : $('#fancybox form').serialize(),
            type    : 'POST',
            success : function( data ){
                $('#questions .results').html( data );
                $.fancybox.close(true);
            }
        });
    }
    return false;
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

//Sauvegarde les références de l'objet et du contenu
function saveReferences( chapitre, question )
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
            if( chapitre != null)
                $('#chapitres #chapitre-'+chapitre+' > .dd3-content .text-muted span').html( data.note );
            else
                $('#questions #question-'+question+' > .dd3-content .text-muted span').html( data.note );

            loader.finished();
            $.fancybox.close(true);
        }
    });
}