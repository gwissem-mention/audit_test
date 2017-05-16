$(document).ready(function() {
    $('.deleteUploadedFile').on('click',function(){
        // $(this).hide();
        $('.uploadedFile').hide();
        $('.inputUpload').show();
        // $('#' + $(this).data('path') ) .val('');
    });

    $('#suggestion_file').on('change', function() {
        $.ajax({
            url  : $('#suggestion-exist-file-url').val(),
            data : {
                fileName : $(this).val()
            },
            type     : 'POST',
            dataType : 'json',
            success  : function( data ){
                if( data.success )
                    apprise('Attention, ce nom de fichier existe déjà, il sera donc écrasé.')
            }
        });
    });

    var contenuId = $('#current-content-id').attr('data-current-id');
    $('[data-content="' + contenuId + '"]').parents('ul').slideDown();

    var IS_PDF = ('1' == $('body').attr('data-is-pdf'));

    /* Gestion de l'ouverture/fermeture du sommaire et de la liste des ambassadeurs */
    $('#sommaire.closed, #ambassadeurs.closed').click(function(){
        //on ouvre
        if ( $(this).hasClass('closed') ){
            wrapper = $('<div />').addClass('lock-screen');
            $('body').append(wrapper);
        //on ferme
        }else{
            $('.lock-screen').remove();
        }

        $(this).toggleClass('closed open');
        $(this).find('.content').toggle();
    });

    // Met en place le carousel
    $('.carousel').carousel({
        interval : 50000000
    });

    // Met en place la pagination
    $('.carousel').each(function(){
        $(this).find('.carousel-inner .item:first').addClass('active');
        $(this).find('.carousel-indicators li.pos-1').addClass('active');
    });

    if (!IS_PDF) {
        $('#autresResultats h2').click(function(){
            $(this).toggleClass('open closed');
            $('#autresResultats .row').slideToggle();
        });

        $('#productions-associes h2').click(function(){
            $(this).toggleClass('open closed');
            $('#productions-associes .row').slideToggle();
        });

        $('#commentaires h2').click(function(){
            $(this).toggleClass('open closed');
            $('#commentaires .bloc-commentaire').slideToggle();
        });

        $('#resultats #pointsdurs h3').click(function(){
            $(this).toggleClass('open closed');
            $('#resultats #pointsdurs .results').slideToggle();
        });

        $('#resultats #productions h3').click(function(){
            $(this).toggleClass('open closed');
            $('#resultats #productions .results').slideToggle();
        });
        $('#resultats #infradocs h3').click(function(){
            $(this).toggleClass('open closed');
            $('#resultats #infradocs .results').slideToggle();
        });
    }

    $('#resultats #pointsdurs h3').click();
    $('#resultats #productions h3').click();
    $('#resultats #infradocs h3').click();

    //Style WYSIWYG custom : titre pliable
    $('h2 .titre_depliable').click(function(){
        $(this).parent().nextAll().each(function(){
            if ( !$(this).find('span').hasClass('titre_depliable') )
                $(this).slideToggle();
            else
                return false;
        });
    });

    //Default collapse all
    $('h2 .titre_depliable').each(function(){
        $(this).parent().nextAll().each(function(){
            if ( !$(this).find('span').hasClass('titre_depliable') )
                $(this).hide();
        });
    });

    $('a.synthese').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'scrolling' : 'no'
    });

    //bind de Validation Engine
    if( $('form.toValidate').length > 0 )
        $('form.toValidate').validationEngine();

    //tooltip sur les mot trouvés du glossaire
    $(".glosstool").tooltip({
        placement : 'top'
    });

    // ************* NOTES *****************

    //Uniquement si les notes sont autorisées sur l'objet courant
    var notesAutorisees = $("#notesAutorisees").val() == "1";

    if(notesAutorisees)
    {
        calculMoyenne();

        //Initialisation du vote
        $('#note-etoile').rateit({ max: 5, step: 1 });

        //Mise à jour des
        $("#note-etoile").bind('rated', function (event, value) {
            $('#note-valeur').val(value);
            $('#note-valeur-show').text(value);
            sauvegardeNote();
        });
        $("#note-etoile").bind('reset', function () {
            $('#note-valeur').val(0);
            deleteNote();
        });
    }


    // Sommaire : Toggle
    $('.toggle-children').click(function() {
        var contenuId = $(this).attr('data-contenu');
        var childrenContainerIsOpen = !$(this).find('.fa').hasClass('fa-plus-circle');

        if (childrenContainerIsOpen) {
            $(this).find('.fa').removeClass('fa-minus-circle');
            $(this).find('.fa').addClass('fa-plus-circle');
        } else {
            $(this).find('.fa').removeClass('fa-plus-circle');
            $(this).find('.fa').addClass('fa-minus-circle');
        }

        $('ul[data-contenu="' + contenuId + '"]').slideToggle();
    });

    $('#note-moyenne-etoile')
        .rateit({
            step: 0.5,
            max: 5,
            ispreset: true,
            readonly: $('#bloc-notation-objet').length === 0
        })
        .rateit('resetable', false)
    ;
    $("#note-moyenne-etoile").bind('rated', function (event, value) {
        $('#note-valeur').val(value);
        $('#note-valeur-show').text(value);
        $('#note-etoile').rateit('value', value);
        sauvegardeNote();
    });

});

//fancybox daffichage de la synthese
enquire.register("screen and (max-width: 991px)", {
    match : function() {
        $(function() {
            $(document).unbind('click.fb-start');
            $('a.synthese').attr('target','_blank');
        });
    },
    unmatch : function() {
        $(function() {
            $('a.synthese').fancybox({
                'padding'   : 0,
                'autoSize'  : false,
                'width'     : '80%',
                'scrolling' : 'no'
            });
            $('a.synthese').attr('target','');
        });
    }
});

function deleteWithConfirm(path)
{
    apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if(r) {
            $.ajax({
                url      : path,
                type     : 'POST',
                dataType : 'json',
                success  : function( data ){
                   location.reload();
                }
            });
        }
    });
}

function ajoutCommentaire(path)
{
    var loader = $('#form-ajout').nodevoLoader();

    if ( $('#form-ajout form').validationEngine('validate') ) {
        loader.start();

        $.ajax({
            url     : path,
            data    :  $('#form-ajout form').serialize(),
            type    : 'POST',
            success : function( data ){
                //Ajout de la réponse
                $('#nouveau-commentaire').append( data );
                loader.finished();
                $('#info-com').html(' - Merci pour votre commentaire !');
            }
        });
    }
}

//Sauvegarde ajax de la note
function sauvegardeNote()
{
    //Mise en place d'un loader le temps de la sauvegarde
    var loader = $("#bloc-notation-objet .wrapper").nodevoLoader();
    loader.start();

    $.ajax({
        url  : $('#note-sauvegarde-url').val(),
        data : {
            objetId   : $('#objetId').val(),
            note      : $('#note-valeur').val(),
            isContenu : $('#isContenu').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            calculMoyenne();

            $("#bloc-notation-objet .message-notation").html('<span class="label label-success">Merci d\'avoir noté.</span>');

            setTimeout(function(){
                $("#bloc-notation-objet .message-notation").html('');
            }, 3000);

            loader.finished();
        }
    });
}

//Calcul JS de la note moyenne
function calculMoyenne()
{
    $.ajax({
        url  : $('#note-moyenne-url').val(),
        data : {
            objetId   : $('#objetId').val(),
            isContenu : $('#isContenu').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function(data) {

            //Mise à jour du nombre de note
            $("#info-note-moyenne").text("(" + data.nbNote + " notes)");
            $("#info-note-moyenne").css('cursor', 'pointer');
            if (data.reviewByMark.length > 0) {

                var html = '<ul style="list-style-type: none; margin: 0; padding: 0;">';
                for (var i = 0; i < data.reviewByMark.length; i++) {
                    plural = '';
                    if (data.reviewByMark[i].reviewCount > 1) {
                        plural = 's';
                    }
                    var li = '<li>' + data.reviewByMark[i].reviewCount + ' note' + plural + ' à ' + data.reviewByMark[i].note + '/5</li>';
                    html = html + li;
                }
                html = html + '</ul>';
            }

            $("#info-note-moyenne").prop('title', html).tooltip({'html': true});

            $('#note-moyenne-etoile')
                .rateit('value', data.noteMoyenne)
            ;

            if (false === data.userCanVote) {
                $('#bloc-notation-objet').hide();
            }
        }
    });
}

function deleteNote()
{
    //Mise en place d'un loader le temps de la sauvegarde
    var loader = $("#bloc-notation-objet .wrapper").nodevoLoader();
    loader.start();

    $.ajax({
        url  : $('#note-delete-url').val(),
        data : {
            objetId   : $('#objetId').val(),
            isContenu : $('#isContenu').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            calculMoyenne();
            loader.finished();
        }
    });
}
