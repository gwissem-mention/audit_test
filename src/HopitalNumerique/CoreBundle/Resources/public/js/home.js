$(document).ready(function(){
    $('#slide').slick({
        adaptiveHeight:true,
        dots: true,
        autoplay: true,
        autoplaySpeed:10000,
        infinite:true,
        prevArrow: "<i class='fa fa-chevron-circle-left slick-prev-custom'></i>",
        nextArrow: "<i class='fa fa-chevron-circle-right slick-next-custom'></i>",
        responsive: [
            {
                breakpoint: 550,
                settings: {
                    arrows:false
                }
            }
        ]
    });
    $('.block-home-menu').hover(
            function() {
                $(this).find('p').animate( {'height':"0px", 'display':'none'}, { queue:false, duration:500 });
            },
            function() {
                $(this).find('p').animate( {'height':"166px", 'display':'block'}, { queue:false, duration:500 });
            }
    );

    $('.onbottomdown').click(function() {
        $(this).hide();
        $('.onbottomup').show();
    });

    $('.onbottomup').click(function() {
        $(this).hide();
        $('.onbottomdown').show();
    });

    
    //Cache le bloc de références
    $('#redirection-references').hide();
    //Cache toutes les questions
    $('#expression-du-besoin .expression-du-besoin-form').each(function(){
        $(this).hide();
    });

    $('#question-precedente').hide();

    //Ré-Affiche la première question (l'ordre le plus bas)
    $('#expression-du-besoin .expression-du-besoin-form').first().show();

    $('#block-carte-france').hover(
        function() {
            $('.carte-france-hover').stop().fadeOut(200);
        },
        function() {
            $('.carte-france-hover').stop().fadeIn(200);
        }
    )
    if($( window ).width() > 990 )
    {
        $("#slide").height($("#sidebar").height());
        $("#slide .slick-list").height($("#sidebar").height());
        $("#slide .slick-list .slick-track").height($("#sidebar").height());
        $("#slide .slick-list .slick-track .image-slide").height($("#sidebar").height());
    }
});

$( window ).resize(function() {
    if($( window ).width() > 990 )
    {
        $("#slide").height($("#sidebar").height());
        $("#slide .slick-list").height($("#sidebar").height());
        $("#slide .slick-list .slick-track").height($("#sidebar").height());
        $("#slide .slick-list .slick-track .image-slide").height($("#sidebar").height());
    }
});

function retourDerniereQuestion(idQuestion)
{
    //Création du loader
    var loader = $('#expression-du-besoin').nodevoLoader().start();

    var arrayClics = $.parseJSON($("#order-clic").val());
    $('#expression-du-besoin').find('#expBesoin-' + arrayClics[0]).show();
    $('#expression-du-besoin').find('#expBesoin-' + idQuestion).hide();
    
    arrayClics.shift();

    if(arrayClics.length === 0)
        $('#question-precedente').hide();

    //Mise à jour de l'input hidden
    $("#order-clic").val(JSON.stringify(arrayClics));
    loader.finished();
}

function clickReponse( idReponse, idQuestion )
{
    var loader = $('#expression-du-besoin').nodevoLoader().start();

    //Sauvegarde du clic sur la réponse en base
    sauvegardeClicStat(idReponse);

    //Récupération des réponses
    var reponses = $.parseJSON($('#reponses-json').val());

    //Récupération de la réponse courante
    var reponsesCourante = reponses[idReponse];

    //Cache la question courante
    //$(this).parents('.expression-du-besoin-form').hide();
    $('#expression-du-besoin').find('#expBesoin-' + idQuestion).hide();

    //Vérifie si on pointe sur une nouvelle question ou si on doit rediriger vers la recherche
    if(reponsesCourante['autreQuestion'])
    {
        //Affiche celle sur laquelle la réponse pointe
        $('#expression-du-besoin').find('#expBesoin-' + reponsesCourante['idQuestion']).show();

        var arrayClics = $.parseJSON($("#order-clic").val());
        arrayClics.unshift(idQuestion);
        //Mise à jour de l'input hidden
        $("#order-clic").val(JSON.stringify(arrayClics));
        $('#question-precedente').show();

        loader.finished();
    }
    else
    {
        var path   = $('#url-modification-session-recherche').val();

        //Génération du cookie JS pour la recherche
        $.ajax({
            url      : path,
            data : {
                id : idReponse
            },
            type     : 'POST',
            dataType : 'json',
            success : function( data ){
                if( data.success )
                {
                    parent.location = $("#url-recherche").val();                    
                }
                else
                {
                    apprise('Une erreur est survenue lors du chargement du calcul de votre recherche, merci de réessayer.');
                    loader.finished();
                }
            }
        });
    }
}

function sauvegardeClicStat( idReponse )
{
    var path   = $('#url-sauvegarde-clic-stat').val();

    $.ajax({
        url      : path,
        data : {
            id : idReponse
        },
        type     : 'POST',
        dataType : 'json',
        success : function( data ){
        }
    });
}