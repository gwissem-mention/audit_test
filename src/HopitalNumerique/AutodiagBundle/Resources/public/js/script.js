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

    if( $('.calcPonderation').length > 0 ){
        //Calcul la pondération pour les questions
        $('.calcPonderation').click(function(){
            $.ajax({
                url      : $('#calc-ponderation-url').val(),
                type     : 'POST',
                dataType : 'json',
                success  : function( data ){
                    apprise(data.message);
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
    if( $('.fancy').length > 0 ){
        $('.fancy').fancybox({
            'padding'   : 0,
            'autoSize'  : false,
            'width'     : '80%',
            'height'    : '600px',
            'scrolling' : 'no',
            'modal'     : true
        });
    }
    
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
    checkAliasUnique();
    if( $('#hopitalnumerique_autodiag_chapitre_alias').parent().parent().find('.help-block').html() == '' ){
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
                if( data.substring(0, 11) != 'ponderation' ){
                    $('#questions .results').html( data );
                    $.fancybox.close(true);
                }else{
                    $('#hopitalnumerique_autodiag_question_ponderation').parent().parent().find('.help-block').html('<span style="color:red">Valeur maximum autorisée : ' + data.substring(12) + '</span>');
                }
            }
        });
    }
    return false;
}

//vérifie que l'alias du chapitre est bien unique par rapport à l'objet
function checkAliasUnique()
{
    $.ajax({
        url  : $('#check-alias-unique-url').val(),
        data : {
            alias : $('#hopitalnumerique_autodiag_chapitre_alias').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            if( !data.success )
                $('#hopitalnumerique_autodiag_chapitre_alias').parent().parent().find('.help-block').html('<span style="color:red"> L\'alias doit être unique</span>');
            else
                $('#hopitalnumerique_autodiag_chapitre_alias').parent().parent().find('.help-block').html('');
        }
    });
}

//Met à jour le nombre d'enfants sélectionés dans la popin
function updateNbChilds()
{
    $('#references-tab .ref').each(function(){
        childs          = $(this).data('childs');
        parentLevel     = $(this).data('level');
        nbChecked = 0;
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
                $('#chapitres #chapitre-'+chapitre+' > .dd3-content .text-muted span').html( data.nbRef );
            else
                $('#questions #question-'+question+' > .dd3-content .text-muted span').html( data.nbRef );

            loader.finished();
            $.fancybox.close(true);
        }
    });
}