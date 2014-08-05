var loader;

$(document).ready(function() {
    calculNoteMoyenne();

    //Pour chaque note, setage du slider avec la valeur associée
    $('.note').each(function(){

        //Récupération du tableau de note jsonifié
        var notes = jQuery.parseJSON( $("#note-json").val() );
    
        //Slider
        $(this).children( ".slider-range-min" ).slider({
            range: "min",
            value: notes[$(this).data('id')],
            min: 0,
            max: 100,
            //A la création on set les couleurs
            create: function(event, ui){
                var couleur;
                //Choix de la couleur à appliquer
                if(parseInt(notes[$(this).parent('.note').data('id')], 0) < 33)
                    couleur = "red";
                else if(parseInt(notes[$(this).parent('.note').data('id')], 0) < 67)
                    couleur = "yellow";
                else
                    couleur = "green";

                $( "#pourcentage-" + $(this).parent('.note').data('id') ).attr("class", function(i, val){
                    return 'pourcentage ' + couleur;
                });
            },
            //Sauvegarde de la note en base
            stop: function(event, ui){
                loader = $(this).parent('.note').nodevoLoader();
                loader.start();

                $.ajax({
                    url      : $("#sauvegarde-note-url").val(),
                    data     : {
                        idObjet                  : $(this).parent('.note').data('id'),
                        value                    : ui.value,
                        rechercheParcoursDetails : $("#etape-selected").val()
                    },
                    type     : 'POST',
                    dataType : 'json',
                    success : function( data ){
                        loader.finished();
                    }
                });
            },
            //Calcul de la moyenne + modifs de la couleur
            slide: function( event, ui ) {
                var couleur;
                //Choix de la couleur à appliquer
                if(parseInt(ui.value, 0) < 33)
                    couleur = "red";
                else if(parseInt(ui.value, 0) < 67)
                    couleur = "yellow";
                else
                    couleur = "green";

                $( "#pourcentage-" + $(this).parent('.note').data('id') ).val( ui.value );
                $( "#pourcentage-" + $(this).parent('.note').data('id') ).attr("class", function(i, val){
                    return 'pourcentage ' + couleur;
                });
                calculNoteMoyenne();
            }
        });
        
        //Label
        $( "#pourcentage-" + $(this).data('id') ).val( $( "#slider-range-min-" + $(this).data('id') ).slider( "value" ) );
    });
});

//Fancybox de la synthese
$('a.synthese').fancybox({
    'padding'   : 0,
    'autoSize'  : false,
    'width'     : '80%',
    'scrolling' : 'no'
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

function calculNoteMoyenne()
{
    var noteTotal = 0;
    var compteur  = 0;

    $(".resultats-points-durs .note .pourcentage").each(function(){
        noteTotal += parseInt($(this).val(),0);
        compteur++;
    });

    var couleur;
    //Choix de la couleur à appliquer
    if(compteur == 0)
        couleur = "none";
    else if( parseInt(noteTotal / compteur, 0) < 33)
        couleur = "red";
    else if(parseInt(noteTotal / compteur, 0) < 67)
        couleur = "yellow";
    else
        couleur = "green";

    //Calcul de la moyenne
    if(compteur != 0)
    {
        $("#note-moyenne span").html(parseInt(noteTotal / compteur, 0) + ' %');
        $("#note-moyenne span").attr("class", function(i, val){
            return couleur;
        });
        $("#recherche-par-parcours-details .en-tete .en-tete-scroll .chemin-de-fer .bloc-etape-selected a").attr("class", function(i, val){
            return couleur;
        });
    }
    else
    {
        $("#note-moyenne span").html(parseInt("0", 0) + ' %');
        $("#note-moyenne span").attr("class", function(i, val){
            return couleur;
        });
        $("#recherche-par-parcours-details .en-tete .en-tete-scroll .chemin-de-fer .bloc-etape-selected a").attr("class", function(i, val){
            return couleur;
        });
    }
}