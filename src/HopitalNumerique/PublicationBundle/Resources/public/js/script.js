$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    $('.deleteUploadedFile').on('click',function(){
        $('.uploadedFile').hide();
        $('.inputUpload').show();
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

    var contenuId = $('#current-content-id').attr('data-current-id'),
        parentsUl = $("[data-content='" + contenuId + "']").parents('ul'),
        selector = undefined !== parentsUl[0] ? parentsUl[0].querySelector("[data-content='" + contenuId + "']") : null,
        toggleChildren = [];

    parentsUl.slideDown();

    if (null !== selector) {
        for (var i = 0; i < parentsUl.length - 1; i++) {
            toggleChildren[i] = parentsUl[i].parentNode.getElementsByClassName('toggle-children')[0];
        }
        toggleElements(toggleChildren);
    }

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
        $('#note-moyenne-etoile')
            .rateit({
                step: 0.5,
                max: 5,
                ispreset: true,
                readonly: $('#bloc-notation-objet').length === 0,
                resetable: false
            })
        ;
        $('#note-etoile').rateit({ max: 5, step: 1 });

        $("#note-moyenne-etoile, #note-etoile").bind('rated', function (event, value) {
            tryRate(value);
        });
        $("#note-etoile").bind('reset', function () {
            $('#note-valeur').val(0);
            deleteNote();
        });
    }


    // Sommaire : Toggle
    $('.toggle-children').click(function() {
        var contenuId = $(this).attr('data-contenu');
        var $fa = $(this).find('.fa');

        $fa.toggleClass('fa-minus-circle');
        $fa.toggleClass('fa-plus-circle');

        $('ul[data-contenu="' + contenuId + '"]').slideToggle();
    });

    Array.prototype.forEach.call(document.querySelectorAll('.toggle'), function (elem) {
        $(elem).toggles({
            on: elem.dataset.active === 'true',
            drag: false,
            text: {on: 'OUI', off: 'NON'},
        }).on('toggle', function (e, active) {
            $.ajax({
                url: this.dataset.path,
                method: 'POST',
                data: {
                    'wanted': active
                },
                complete: function(data) {
                    console.log(data);
                    if (data.status === 301) {
                        window.location = data.responseJSON.redirect;
                    }
                }
            });
        })
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

/**
 * Show popin if rate is under 3
 * @param int rate
 */
function tryRate(rate) {
    $('#note-valeur').val(rate);
    $('#note-valeur-show').text(rate);
    $('#note-etoile').rateit('value', rate);
    if (parseFloat(rate) < 3) {
        var averageRateStar = document.getElementById('note-moyenne-etoile');
        $.ajax({
            url  : averageRateStar.dataset.url,
            type     : 'POST',
            success  : function( data ){
                $.fancybox({
                    content: data,
                    autoSize: false,
                    autoHeight: true,
                    width: 600,
                    title: averageRateStar.dataset.title,
                    afterShow:function() {
                        $('form[name="note_commentaire"]').validationEngine({
                            promptPosition: 'bottomLeft',
                            scroll: false
                        });
                        document.querySelector('form[name="note_commentaire"]').addEventListener('submit', function (ev) {
                            if ($('form[name="note_commentaire"]').validationEngine('validate')) {
                                sauvegardeNote(document.getElementById('note_commentaire_comment').value);
                                $.fancybox.close(true);
                            }
                        });
                        document.querySelector('button[name="note_commentaire[cancel]"]').addEventListener('click', function (ev) {
                            $.fancybox.close(true);
                            calculMoyenne();
                        })
                    }
                });
            },
            error   : function (xhr, textStatus, error) {
                alert(averageRateStar.dataset.error);
            }
        });
    } else {
        sauvegardeNote();
    }
}

//Sauvegarde ajax de la note
function sauvegardeNote(commentaire)
{
    commentaire = typeof commentaire !== 'undefined' ? commentaire : null;

    //Mise en place d'un loader le temps de la sauvegarde
    var loader = $("#bloc-notation-objet .wrapper").nodevoLoader();
    loader.start();

    $.ajax({
        url  : $('#note-sauvegarde-url').val(),
        data : {
            objetId   : $('#objetId').val(),
            note      : $('#note-valeur').val(),
            isContenu : $('#isContenu').val(),
            comment: commentaire
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
        },
        error   : function (xhr, textStatus, error) {
            alert('Veuillez vous connecter pour attribuer une note');

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

function toggleElements(elements)
{
    for (var i = 0; i < elements.length; i++) {
        var $fa = $(elements[i]).find('.fa');
        $fa.toggleClass('fa-minus-circle');
        $fa.toggleClass('fa-plus-circle');
    }
}
