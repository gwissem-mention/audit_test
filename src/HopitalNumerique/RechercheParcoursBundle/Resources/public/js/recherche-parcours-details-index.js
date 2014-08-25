var loader;

$(document).ready(function() {
    calculNoteMoyenne();

    $("#recherche-par-parcours-details .chemin-de-fer li a").tooltip({ position: { my: "center bottom-15", at: "top center" } });

    //Pour chaque note, setage du slider avec la valeur associée
    $('.note').each(function(){

        //Récupération du tableau de note jsonifié
        var notes = jQuery.parseJSON( $("#note-json").val() );
    
        //Slider
        $(this).children( ".slider-range-min" ).slider({
            range: "min",
            value: notes[$(this).data('id')],
            min : 0,
            step: 5,
            max : 100,
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
    
    // -- vvv -- Gestion des checkboxs "Non concerné" -- vvv --
    $("#pointsdurs .nonConcerne input.checkbox").each(function(){

        //Initialisation du volet
        var idNote    = $(this).data('id');
        var isChecked = $(this).attr('checked');

        if(isChecked)
            $("#volet-" + idNote).show();
        else
            $("#volet-" + idNote).hide();

        //Init de la fonction click
        $(this).change(function(){
            var idNote    = $(this).data('id');
            var isChecked = $(this).attr('checked');

            //Set manuel de la checkbox
            if(isChecked)
                $(this).removeAttr('checked');
            else
                $(this).attr('checked','checked');

            //Récupération de la nouvelle valeur
            var isChecked = $(this).attr('checked');

            if(isChecked)
                $("#volet-" + idNote).show();
            else
                $("#volet-" + idNote).hide();

            loader = $("#nonConcerne-" + idNote).nodevoLoader();
            loader.start();

            $.ajax({
                url      : $("#sauvegarde-nonconcerne-url").val(),
                data     : {
                    idObjet                  : $(this).data('id'),
                    value                    : isChecked,
                    rechercheParcoursDetails : $("#etape-selected").val()
                },
                type     : 'POST',
                dataType : 'json',
                success : function( data ){
                    loader.finished();
                    calculNoteMoyenne();
                }
            });
        });
    });
    // -- ^^^ -- Gestion des checkboxs "Non concerné" -- ^^^ --

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

        var idNote = $(this).data('id');

        if(! $("#checkbox-" + idNote ).attr('checked'))
        {
            noteTotal += parseInt($(this).val(),0);
            compteur++;
        }
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
        $("#recherche-par-parcours-details .en-tete .en-tete-scroll .chemin-de-fer .bloc-etape-selected a").attr('title','Taux de maîtrise de l\'étape : '+ parseInt(noteTotal / compteur, 0) +' %');
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
        $("#recherche-par-parcours-details .en-tete .en-tete-scroll .chemin-de-fer .bloc-etape-selected a").attr('title','Taux de maîtrise de l\'étape : '+ parseInt("0", 0) +' %');
        $("#recherche-par-parcours-details .en-tete .en-tete-scroll .chemin-de-fer .bloc-etape-selected a").attr("class", function(i, val){
            return couleur;
        });
    }
}