jQuery(document).ready(function() {
    //bind de Validation Engine
    $('form.toValidate').validationEngine();
});

$(function() {
    $('.dd3-content a, .manageReferences, .uploadSommaire').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'scrolling' : 'no',
        'modal'     : true
    });
});

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

function transfertPost( url ){
    $.ajax({
        url  : url,
        data : {
            boardId : $("#transfert-topic").val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
           window.location.reload();
        }
    });
}

//Sauvegarde les références du topic et du contenu
function saveReferences( topic, idContenu )
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

    if( topic )
        ajaxUrl = $('#save-references-topic-url').val();
    else
        ajaxUrl = $('#save-references-contenu-url').val();

    //save the value
    $.ajax({
        url  : ajaxUrl,
        data : {
            references : json,
            contenu    : idContenu
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            //update ref number
            if(topic)
                $('.nbRefs').html( data.note );    
            else
                $('#sommaire #contenu-'+idContenu+' > .dd3-content .text-muted span').html( data.note );
            
            loader.finished();
            $.fancybox.close(true);
        }
    });
}