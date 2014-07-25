$(document).ready(function() {
    
    //Création et gestion de l'arborescence des chapitres
    if( $('#recherchesParcours').length > 0 ){
        $('#recherchesParcours').nestable({'maxDepth':1,'group':0}).on('change', function() {
            var serializedDatas = $(this).nestable('serialize');

            $.ajax({
                url  : $('#order-recherche-parcours-url').val(),
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
            'height'    : '474px',
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


//Supprime le contenu en cours de visualisation
function deleteDetails( id, url )
{
    apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if(r) { 
            $.ajax({
                url  : url,
                data : {
                    id : id
                },
                type     : 'POST',
                success  : function( data ){
                    if( data.success ){
                        //Ajout dans la liste déroulante de l'entité supprimé
                        $(".blocQuestion select").append('<option value="' + data.refId +'">'+ data.refLibelle +'</option>');

                        //supprime l'élément dans le HTML
                        $('#details #details-' + id).remove();

                        if( $('#details ol li').length == 0)
                            $('#details .designForBlank').show();
                    }
                }
            });
        }
    });
}

//Selectionne un chapitre et charge l'ensemble des questions liés
function selectRecherche( id, url )
{
    $('#details .selectionQuestion').hide();
    
    //bind de Validation Engine
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();

    $.ajax({
        url     : url,
        type    : 'POST',
        success : function( data ){
            $('#details .results').html( data );
        }
    });

    $('#details .question').val( id );
}

//Selectionne un chapitre et charge l'ensemble des questions liés
function addDetails( url )
{
    if ( $('#designForForm form').validationEngine('validate') ) {
        $.ajax({
            url     : url,
            data    :  $('#designForForm form').serialize(),
            type    : 'POST',
            success : function( data ){
                //Ajout de la réponse
                $('#details-dd ol:first').append( data );
                //Supprime l'élément de la liste
                $(".blocQuestion option:selected").remove();
            }
        });
    }
}
function saveDetails( url )
{
   $.ajax({
        url     : url,
        data    : 
        {
            description: $('#description_detail').val()
        },
        type    : 'POST',
        dataType : 'json',
        success : function( data ){
            $.fancybox.close(true);
        }
    });
}