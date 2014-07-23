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
                    datas : serializedDatas,
                },
                type     : 'POST',
                dataType : 'json',
                success  : function( data ){
                    //console.log( 'reorder executed' );
                }
            });
        });
    }
    
    //bind de Validation Engine
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();
});

//ajoute une question
function addQuestion( titre )
{
    $.ajax({
        url  : $('#add-btn-question-url').val(),
        data : {
            titre : titre,
            idQuestionnaire : $('#id-questionnaire').val()
        },
        type     : 'POST',
        success  : function( data ){
            if( data != '' ){
                $('#questions ol:first').append( data );

                if( $('#questions ol li').length > 0)
                {
                    $('.designForBlank').hide();
                }

                //Forcer le click sur la question ajoutée
                var derniereLigne = $('#questions ol li').last();
                var idQuestion    = derniereLigne.data('id');

                selectQuestion(idQuestion, $('#select-question-url-' + idQuestion).val());

            }else
                apprise('Une erreur est survenue lors de l\'ajout de votre question, merci de réessayer.');
        }
    });
}

//sauvegarde la question
function saveQuestion( url )
{
    $.ajax({
        url      : url,
        data     : 
        {
            id              : $('#idQuestion').val(),
            libelle         : $('#libelle_question').val(),
            typeQuestion    : $('#typeQuestion').val(),
            refTypeQuestion : $('#typeQuestion_reference').val(),
            obligatoire     : $('#questionObligatoire').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            if( data.success ){
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
                        location.reload();
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