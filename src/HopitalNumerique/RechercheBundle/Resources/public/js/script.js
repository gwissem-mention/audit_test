var DELAY = 200, clicks = 0, timer = null, showPlaceholder = true;

var ajaxRequeteResultat;
var hasResultat =false;

$(document).ready(function() {
    var hasResultat =false;
    $("#bloc_filtres").hide();
    $("#bloc_exalead").removeClass();
    $("#bloc_exalead").addClass('col-md-12');

    //Gestion de l'ajout de critères dans la requete
    $('#origin li span').on("click", function(e){
        clicks++; //count clicks

        //collapse click
        if(clicks === 1) {
            that  = this;
            timer = setTimeout(function() {
                $(that).parent().toggleClass("active");
                $(that).parent().find('i.pull-right').toggleClass("fa-chevron-down fa-chevron-right");
                $(that).parent().find('ol:first').slideToggle({duration: 200});

                //accordeon
                $(that).parent().siblings().each(function(){
                    $(this).removeClass("active");
                    $(this).find('i.pull-right').addClass("fa-chevron-right").removeClass("fa-chevron-down");
                    $(this).find('ol:first').slideUp({duration: 200});
                });
            

                clicks = 0; //after action performed, reset counter
            }, DELAY);
        //add click
        } else {
            clearTimeout(timer); //prevent single-click action
            success = selectElement( $(this).parent() ); //add element to DEST
            //placeholder management
            if( success && showPlaceholder){
                $(".placeholder").hide();
                $("#bloc_filtres").show();
                $("#bloc_exalead").removeClass();
                $("#bloc_exalead").addClass('col-md-6');
                showPlaceholder = false;
                $("#dest").removeClass('hide');
                $(".requete h2").addClass('ropen');
            }

            //remove Cookie after each Ref Added/Removed
            $.removeCookie('showMorePointsDurs', { path: '/' });
            $.removeCookie('showMoreProductions', { path: '/' });
            
            if( !$(this).parent().hasClass('level0') )
            {
                updateResultats( false );
            }

            clicks = 0; //after action performed, reset counter
        }
    })
    .on("dblclick", function(e){
        e.preventDefault(); //cancel system double-click event
    });

    
    //Gestion du simple click sur le petit +
    $('#origin li i.fa-plus-circle').on("click", function(e){
        success = selectElement( $(this).parent() ); //add element to DEST
        //placeholder management
        if( success && showPlaceholder){
            $(".placeholder").hide();
            $("#bloc_filtres").show();
            $("#bloc_exalead").removeClass();
            $("#bloc_exalead").addClass('col-md-6');
            showPlaceholder = false;
            $("#dest").removeClass('hide');
            $(".requete h2").addClass('ropen');
        }

        //remove Cookie after each Ref Added/Removed
        $.removeCookie('showMorePointsDurs', { path: '/' });
        $.removeCookie('showMoreProductions', { path: '/' });
        
        if( !$(this).parent().hasClass('level0') )
        {
            updateResultats( false );
        }
    });

    
    //Gestion de la suppression de critères dans la requete
    $('.arbo-requete span').on("click", function(e){
        removeElement( $(this).parent() ); //remove element from DEST

        //remove Cookie after each Ref Added/Removed
        $.removeCookie('showMorePointsDurs', { path: '/' });
        $.removeCookie('showMoreProductions', { path: '/' });
            
        updateResultats( false );

        resetRequete();
    });

    
    //toggle des paramètres de la requete
    $('.requete h2').on('click', function(){
        if( $(this).hasClass('ropen') || $(this).hasClass('rclose') ) {
            $(this).toggleClass('ropen rclose');
            $('#blocRequeteRecherche').slideToggle({duration: 200});
        }
    });

    $('#recherche .alertExalead span.label').on('click', function(){
        $('#recherche .alertExalead').hide("slow");
    });
    
    //init : open first categ
    $('#origin li.level0:first').addClass('active').find('ol:first').slideDown();

    if( $('#requete-refs').val() != '[]')
        handleRequestForRecherche();

    
    $('a.synthese').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'scrolling' : 'no'
    });
    
    $('#categ_production_select').multiselect({
        nonSelectedText: 'Filtrer par type de production ',
        buttonContainer: '<div class="btn-group" />',
        numberDisplayed: 1,
        buttonWidth: '100%',
        nSelectedText: 'catégories sélectionnées'
    });

    //Récupération 
    var arraySelectCateg = $.parseJSON($("#categ_production_select_vals_chargement").val());
    if(arraySelectCateg.length != 0)
    { 
        $.each(arraySelectCateg, function(){
            $("#categ_production_select").multiselect('select', this, false);
        });
    }

    //vvv Chargement du bloc Requete de recherche en fonction de la Recherche textuelle et de la Recherche par type de production vvv
    var loaderSelecteur = $('#recherche .dropdown-menu').nodevoLoader().start();
    
    //AJAX call for results
    $.ajax({
        url  : $('#resultats-type-production').val(),
        data : {
            categPointDur: $("#categ_production_select").val()
        },
        type    : 'POST',
        success : function( data ){
            $('#arbo-type-prod').html( data );
            loaderSelecteur.finished();
        }
    });

    
    //On enlève le placeholder
    if($("#recherche_textuelle").val() != '')
    {
        if( $(".arbo-requete").find('li:not(.hide)').length == 0 )
        {
            if($(".placeholder-aucunCritere").length == 0)
            {
                /*$('#recherche_textuelle').val('');*/
                $(".arbo-requete").append('<small class="placeholder-aucunCritere"><span class="text-muted">Aucun critère de recherche textuelle.</span></small>');
            }
        }
        else
        {
            $(".placeholder-aucunCritere").remove();
        }
        $(".placeholder").hide();
        $("#bloc_filtres").show();
        $("#bloc_exalead").removeClass();
        $("#bloc_exalead").addClass('col-md-6');
        showPlaceholder = false;
        $("#dest").removeClass('hide');
        $(".requete h2").addClass('ropen');

        updateResultats( true );
    }

    
    //Recherche textuelle
    $("#arbo-recherche-textuelle").html($("#recherche_textuelle").val() == '' ? '<small><span class="text-muted">Aucune recherche textuelle.</span></small>' : '<small><span>' + $("#recherche_textuelle").val() +'</span></small>');
    
    $("#categ_production_select").change(function(){
        $("#categ_production_select_vals").val($(this).val());

        //Mise à jour du cradre "Requete de recherche"
        var loader          = $('#recherche .requete').nodevoLoader().start();
        var loaderSelecteur = $('#recherche .dropdown-menu').nodevoLoader().start();
    
        //AJAX call for results
        $.ajax({
            url  : $('#resultats-type-production').val(),
            data : {
                categPointDur: $(this).val()
            },
            type    : 'POST',
            success : function( data ){
                $('#arbo-type-prod').html( data );

                loader.finished();
                loaderSelecteur.finished();
            }
        });

        updateResultats( true );
    });

    
    $("#recherche_textuelle").change(function(){
        RechercheExalead();
    });

    
    affichagePlaceholder();
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


/**
 * Fonction appellée lors d'une recherche exalead
 */
function RechercheExalead()
{
    $('.recherche_textuelle_avancee').css({ display:'none' });

    //Vérif avant de lancer une requete
    if(($("#recherche_textuelle").val().length < 2 ))
    {
        $('#recherche .alertExalead').show("slow");
        $('#recherche .alertExaleadEtoile').hide("slow");
    }
    else if(($("#recherche_textuelle").val().length <= 3 && $("#recherche_textuelle").val().indexOf("*") >= 0 ))
    {
        $('#recherche .alertExaleadEtoile').show("slow");
        $('#recherche .alertExalead').hide("slow");

    }
    else
    {
        $('#recherche .alertExalead').hide("slow");
        $('#recherche .alertExaleadEtoile').hide("slow");
        if($("#recherche_textuelle").val() != '')
        {
            $(".placeholder").hide();
            $("#bloc_filtres").show();
            $("#bloc_exalead").removeClass();
            $("#bloc_exalead").addClass('col-md-6');
            showPlaceholder = false;
            $("#dest").removeClass('hide');
            $(".requete h2").addClass('ropen');
        }
        else
        {
            if($(".placeholder-aucunCritere").length)
            {
                $('#resultats').html('');
                $(".arbo-requete").find('li').addClass('hide');
                $(".placeholder").show();
                showPlaceholder = true;
                $("#dest").addClass('hide');
                $(".requete h2").removeClass('ropen rclose');
            }
            $(".placeholder-aucunCritere").remove();
        }

        if($(".arbo-requete").find('li:not(.hide)').length == 0 && !$("#dest").hasClass('hide'))
        {
            if($(".placeholder-aucunCritere").length == 0)
            {
                $(".arbo-requete").append('<small class="placeholder-aucunCritere"><span class="text-muted">Aucun critère de recherche textuelle.</span></small>');
            }
        }

        //Mise à jour du cradre "Requete de recherche"
        $("#arbo-recherche-textuelle").html($("#recherche_textuelle").val() == '' ? '<small><span class="text-muted">Aucune recherche textuelle.</span></small>' : '<small><span>' + $("#recherche_textuelle").val() +'</span></small>');

        updateResultats( true );

        resetRequete();
    }
}

/**
 * Prend en compte la requete par défaut ou la requete active
 */
function handleRequestForRecherche()
{
    refs = $.parseJSON( $('#requete-refs').val() );

    $.each(refs, function(key, val){
        $.each(val.reverse(), function( key, item ){
            if( $('#dest .element-'+item).hasClass('hide') )
                selectElement( $('#origin .element-'+item) );
        });
    });

    updateResultats( false );
    resetRequete();
}

/**
 * On sélectionne un critère que l'on veut ajouter dans la requete
 */
function selectElement( item )
{
    if( $(item).hasClass('cliquable') ) {
        //Raye l'élement
        $(item).addClass('selected');
        $(item).find('li').addClass('selected');

        //cache le parent de l'élément d'origine (si c'est le dernier enfant que l'on viens de cacher)
        handleParentsOrigin( $(item) );

        //affiche l'élément dans la liste de droite
        showItemDestRecursive( $(item) );

        //For parent
        $(item).find('li').each(function(){
            $('#dest li.hide.element-' + $(this).data('id') ).removeClass('hide');
        })

        $(".placeholder-aucunCritere").remove();

    }else
        return false;

    return true;
}

/**
 * On cache en mode récursif tous les parents de l'élément
 */
function handleParentsOrigin( item )
{
    //alors on check si le LI de la liste parente n'est pas le level0
    if( $(item).parent().parent().hasClass('cliquable') ){
        //si le parent de l'élément n'a plus d'enfants cliquables
        if ($(item).parent().find('li.selected').length == $(item).parent().find('li').length) {

            //on strip le LI qui contient la liste des enfants
            $(item).parent().parent().addClass('selected');

            //on check de manière récursive
            handleParentsOrigin( $(item).parent().parent() );
        }
    }
}

/**
 * Affiche les items dans la liste de destination de manière récursive
 */
function showItemDestRecursive( item )
{
    destItem = $('#dest .element-' + $(item).data('id') );
    $(destItem).removeClass('hide');

    if ($(destItem).parent().parent().hasClass('hide')){
        showItemDestRecursive( $(destItem).parent().parent() );
    }
}

/**
 * Supprime les éléments de la liste Destionation et met à jour la liste Origin
 */
function removeElement ( item )
{
    //hide element from Dest
    $(item).addClass('hide');
    $(item).find('li').addClass('hide'); //pour éviter les bugs, on hide tous les enfants de l'élément cliqué

    //recursive hide parents
    handleParentsDestination( $(item) );

    //si j'ai cliqué sur un element enfant
    if ( $(item).find('ol').length == 0  ) {
        showItemOriginRecursive( $(item) ); //=> jaffiche en récursif ma propre arbo
    //si j'ai cliqué sur un element parent (avec des enfants)
    }else{
        //si on est parent, mais pas le level0
        if( !$(item).hasClass('level0') )
            showItemOriginRecursive( $(item) ); //=> jaffiche en récursif ma propre arbo

        $('#origin .element-' + $(item).data('id') + ' li').slideDown().removeClass('selected');
    }
    
    //vérification et retrait de l'item empty pour le premier level
    // $('#origin li.level0').each(function(){
    //     if ( $(this).find('li.cliquable.level1').length > 0)
    //         $(this).find('li.empty').remove();
    // });
}

/**
 * On cache en mode récursif tous les parents de l'élément
 */
function handleParentsDestination( item )
{
    //si le parent de l'élément n'a plus d'enfants affichés
    if( $(item).parent().find('li:not(.hide)').length == 0 ) {

        if( $(item).parent().parent().prop("tagName") == 'LI' ) {
            //alors on remove le LI qui contient la liste des enfants
            $(item).parent().parent().addClass('hide');

            //on check de manière récursive
            handleParentsDestination( $(item).parent().parent() );
        //si l'élément n'est pas de type LI, on est allé trop haut, on réaffiche le placeholder
        }
        else
        {
            if($("#recherche_textuelle").val() == '')
            {
                $(".arbo-requete").find('li').addClass('hide');
                $(".placeholder").show();
                $("#bloc_filtres").hide();
                $("#bloc_exalead").removeClass();
                $("#bloc_exalead").addClass('col-md-12');
                showPlaceholder = true;
                $("#dest").addClass('hide');
                $(".requete h2").removeClass('ropen rclose');

                placeholderExalead();

                resetRequete();
            }
            else
            {
                if( $(".placeholder-aucunCritere").length == 0 )
                {
                    $(".arbo-requete").append('<small class="placeholder-aucunCritere"><span class="text-muted">Aucun critère de recherche textuelle.</span></small>');
                }
            }
        }
    }
}

/**
 * Affiche les items dans la liste d'origine de manière récursive
 */
function showItemOriginRecursive( item )
{
    originItem = $('#origin .element-' + $(item).data('id') );
    $(originItem).slideDown().removeClass('selected');
    
    //si mon parent n'est pas affiché, on l'affiche en mode récursif
    if ( $(originItem).parent().parent().hasClass('selected') && !$(originItem).parent().parent().hasClass("level0") )
        showItemOriginRecursive( $(originItem).parent().parent() );
}

/**
 * Met à jour les résulats trouvés en fonction des paramètres de la requête
 */
function updateResultats( cleanSession )
{
    var loader = $('#resultats').nodevoLoader().start();

    if(ajaxRequeteResultat != null )
    {
        ajaxRequeteResultat.abort();
    }

    //AJAX call for results
    ajaxRequeteResultat = $.ajax({
        url  : $('#resultats-url').val(),
        data : {
            references   : getReferences(),
            cleanSession : cleanSession,
            categPointDur: $("#categ_production_select_vals").val(),
            rechercheTextuelle : $("#recherche_textuelle").val()
        },
        type    : 'POST',
        success : function( data ){
            $('#resultats').html( data );

            hasResultat = $('#resultats').html().trim() !== "";

            if( $('#dest li:not(.hide)').length == 0 && $("#recherche_textuelle").val() == '')
            {
                $('.requete h2').html( 'Requête de recherche' );
                $('#resultats').html('');
            }
            else if( $('#nbResults').val() == 1 || $('#nbResults').val() == 0 )
            {
                $('.requete h2').html( 'Requête de recherche ('+$('#nbResults').val()+' Résultat)' );
            }
            else
            {
                $('.requete h2').html( 'Requête de recherche ('+$('#nbResults').val()+' Résultats)' );
            }

            loader.finished();

            var search = $("#patternFounded").val() == undefined ? '[]' : $("#patternFounded").val();
            if(search != "")
            {
                search = JSON.parse(search);
                $("#resultats p").highlight( search, { wordsOnly: false } );
            }

            placeholderExalead();
        }
    });
}

/**
 * Gestion de moins de résultats
 */
function showLess(that, btn)
{
    //Maj Cookie val
    cookieName = (btn == 1) ? 'showMorePointsDurs' : 'showMoreProductions';
    $.cookie(cookieName, 3, {path: '/' } );

    var i = 0;
    $(that).parent().parent().find('.results > div').each(function( i ){
        if( i !== 0 && i !== 1 && i !== 2)
        {
            $(this).slideUp();
        }
    });

    $(that).parent().find('.showMore').show();
    $(that).hide();
}

/**
 * Gestion du bouton Plus de résultats
 */
function showMore(that, btn)
{
    toHide       = 500;
    elementsLeft = 0;
    cookieName   = (btn == 1) ? 'showMorePointsDurs' : 'showMoreProductions';

    //set Default value if not exist
    if( $.cookie(cookieName) == undefined )
        $.cookie(cookieName, 3, {path: '/' });

    //get cookie val
    showMoreCookieVal = $.cookie(cookieName);

    $(that).parent().parent().find('.results > div:hidden').each(function(){
        if( toHide != 0){
            $(this).slideDown();
            toHide = toHide - 1;
            showMoreCookieVal++;
        }else
            elementsLeft = elementsLeft + 1;
    });

    //Maj Cookie val
    $.cookie(cookieName, showMoreCookieVal, {path: '/' } );

    if (elementsLeft == 0)
        $(that).hide();

    $(that).parent().find('.showLess').show();
}

/**
 * Retourne les références selectionnées pour la requete de recherche
 */
function getReferences()
{
    var references = {'categ1':[],'categ2':[],'categ3':[],'categ4':[]};

    //create array with selected references
    if( !$('#dest .element-220').hasClass('hide') ){
        $('#dest .element-220 li:not(.hide)').each(function(){
            if( !$(this).hasClass('childs') )
                references.categ1.push( $(this).data('id') );
        });
    }
    if( !$('#dest .element-221').hasClass('hide') ){
        $('#dest .element-221 li:not(.hide)').each(function(){
            if( !$(this).hasClass('childs') )
                references.categ2.push( $(this).data('id') );
        });
    }
    if( !$('#dest .element-223').hasClass('hide') ){
        $('#dest .element-223 li:not(.hide)').each(function(){
            if( !$(this).hasClass('childs') )
                references.categ3.push( $(this).data('id') );
        });
    }
    if( !$('#dest .element-222').hasClass('hide') ){
        $('#dest .element-222 li:not(.hide)').each(function(){
            if( !$(this).hasClass('childs') )
                references.categ4.push( $(this).data('id') );
        });
    }

    return references;
}

/**
 * Sauvegarde de la requête de recherche
 */
function saveRequest( user )
{
    if( user ){
        //une requete est déjà selectionnée, au click sur enregistrer on propose soit la création d'une nouvelle requete, soit la mise à jour de celle ci
        if( $('.requeteNom').data('id') != '' ){
            apprise('Enregistrer en tant que nouvelle requête :', {'input':true,'textOk':'Enregistrer','textCancel':'Mettre à jour la requête courante'}, function(r) {
                if( r )
                    handleRequeteSave( r, null );
                else
                    handleRequeteSave( r, $('.requeteNom').data('id') );
            });
        //aucune requete active, on propose l'enregistrement
        }else{
            apprise('Nom de la requête :', {'input':true,'textOk':'Enregistrer','textCancel':'Annuler'}, function(r) {
                if( r )
                    handleRequeteSave( r, null );
            });
        }
    }else
        apprise('Pour enregistrer une requête, vous devez créer au préalable un <a href="'+$('.loggedUser .infos').attr('href')+'">compte utilisateur.</a>');
}

/**
 * Fonction ajax qui gère la création ou la mise à jour de la requete
 */
function handleRequeteSave( r, id )
{
    $.ajax({
        url  : $('#requete-save-url').val(),
        data : {
            nom                : r,
            id                 : id,
            references         : getReferences(),
            categPointDur      : $("#categ_production_select_vals").val(),
            rechercheTextuelle : $("#recherche_textuelle").val(),
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            if(data.success){
                $('.requeteNom').html( data.nom ).slideDown();
                $('.requeteNom').data('id', data.id);
                if( data.add ){
                    if( data.def == '1' ){
                        selected = 'violet';
                        $('#mesrequetes ul li').remove();
                    }else
                        selected = 'fa-inverse';
                    $('#mesrequetes ul').append('<li><a href="'+data.path+'" ><i class="fa fa-star '+selected+'"></i>'+data.nom+'</a></li>');
                }
            }
        }
    });
}


/**
 * Bouton qui permet de clear les éléments filtrés
 */
function cleanRequest()
{
    apprise('Confirmer la réinitialisation de la requête ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if( r ){
            $("#recherche_textuelle").val('');

            $('.arbo-requete li').each( function(){
                removeElement( $(this) );
            });

            $.removeCookie('showMorePointsDurs', { path: '/' });
            $.removeCookie('showMoreProductions', { path: '/' });

            $('.requeteNom').html('');
            $('.requeteNom').data('id', '');

            $('#categ_production_select option').each(function() {
                $("#categ_production_select").multiselect('deselect', $(this).val());
            })
            $('#example-reset').multiselect('refresh');
            $('#categ_production_select_vals').val('');
            $('#categ_production_select_vals_chargement').val('');

            $('#resultats')

            var loader = $('#resultats').nodevoLoader().start();

            if(ajaxRequeteResultat != null )
            {
                ajaxRequeteResultat.abort();
            }

            //AJAX call for results
            ajaxRequeteResultat = $.ajax({
                url  : $('#resultats-url').val(),
                data : {
                    references         : getReferences(),
                    cleanSession       : true,
                    categPointDur      : $("#categ_production_select_vals").val(),
                    rechercheTextuelle : $("#recherche_textuelle").val()
                },
                type    : 'POST',
                success : function( data ){
                    $('.requete h2').html( 'Requête de recherche' );
                    $('#resultats').html('');

                    hasResultat = false;

                    affichagePlaceholder();

                    loader.finished();
                }
            });

            history.pushState({ path: this.path }, '', $('#search-homepage-url').val() );
        }
    });
}

function resetRequete()
{
    if( $(".arbo-requete").find('li:not(.selected)').length == 0 
        && ($("#recherche_textuelle").val() == '') ) 
    {
        var loader = $('#resultats').nodevoLoader().start();
        
        if(ajaxRequeteResultat != null )
        {
            ajaxRequeteResultat.abort();
        } 

        $('#categ_production_select option').each(function() {
            $("#categ_production_select").multiselect('deselect', $(this).val());
        })
        $('#example-reset').multiselect('refresh');
        $('#categ_production_select_vals').val('');
        $('#categ_production_select_vals_chargement').val('');

        //AJAX call for results
        ajaxRequeteResultat = $.ajax({
            url  : $('#resultats-url').val(),
            data : {
                references         : getReferences(),
                cleanSession       : true,
                categPointDur      : $("#categ_production_select_vals").val(),
                rechercheTextuelle : $("#recherche_textuelle").val()
            },
            type    : 'POST',
            success : function( data ){
                $('.requete h2').html( 'Requête de recherche' );
                $('#resultats').html('');

                hasResultat = false;

                affichagePlaceholder();
                
                loader.finished();
            }
        });
    }
}

function resetRequeteOnLoad()
{
    if( $(".arbo-requete").find('li:not(.selected)').length == 0 
        && ($("#recherche_textuelle").val() == '') ) 
    {
        var loader = $('#resultats').nodevoLoader().start();
        
        if(ajaxRequeteResultat != null )
        {
            ajaxRequeteResultat.abort();
        } 

        $('#categ_production_select option').each(function() {
            $("#categ_production_select").multiselect('deselect', $(this).val());
        })
        $('#example-reset').multiselect('refresh');
        $('#categ_production_select_vals').val('');
        $('#categ_production_select_vals_chargement').val('');

        //AJAX call for results
        ajaxRequeteResultat = $.ajax({
            url  : $('#resultats-url').val(),
            data : {
                references         : getReferences(),
                cleanSession       : true,
                categPointDur      : $("#categ_production_select_vals").val(),
                rechercheTextuelle : $("#recherche_textuelle").val()
            },
            type    : 'POST',
            success : function( data ){
                $('.requete h2').html( 'Requête de recherche' );
                $('#resultats').html('');

                hasResultat = false;

                affichagePlaceholder();
                
                loader.finished();
            }
        });
    }
}

/**
 * Fonction permettant de gerer l'affichage du bloc placeholder
 *
 * @return Void
 */
function affichagePlaceholder()
{
    //Cas où on supprime tout les critères de recherche et vide le texte, le hasResultat est encore à true avant le raffraichissement
    //Dans le cas où il n'y a pas de résultat
    if(hasResultat)
    {
        $(".placeholder").hide();
        $("#bloc_filtres").show();
        $("#bloc_exalead").removeClass();
        $("#bloc_exalead").addClass('col-md-6');
        showPlaceholder = false;
        $("#dest").removeClass('hide');
        $(".requete h2").addClass('ropen');
    }
    else if($('#resultats').html().trim() === "")
    {
        $(".arbo-requete").find('li').addClass('hide');
        $(".placeholder").show();
        $("#bloc_filtres").hide();
        $("#bloc_exalead").removeClass();
        $("#bloc_exalead").addClass('col-md-12');
        showPlaceholder = true;
        $("#dest").addClass('hide');
        $(".requete h2").removeClass('ropen rclose');
    }
    else
    {
        $("#bloc_filtres").show();
        $("#bloc_exalead").removeClass();
        $("#bloc_exalead").addClass('col-md-6');
    }

    placeholderExalead();
}

function placeholderExalead()
{
    if(hasResultat)
    {
        $("#recherche_textuelle").attr('placeholder', 'Filtrer les résultats par mots clés');
    }
    else
    {
        $("#recherche_textuelle").attr('placeholder', 'Ou rechercher des résultats par mot-clés');
    }
}


function toggleRechercheAvancee()
{
    $('.recherche_textuelle_avancee').slideToggle(200);
}

/**
 * 
 * @param string chaineRechercheAvancee Chaîne à ajouter dans le champ
 * @param integer texteSelectionDebut Position où commencer à sélectionner le texte
 * @param integer texteSelectionFin Position où terminer à sélectionner le texte (à partir de la fin donc -1 si juste avant le dernier caractère)
 */
function rechercheAvancee(chaineRechercheAvancee, texteSelectionDebut, texteSelectionFin)
{
    var positionInitiale = $('#recherche_textuelle').val().length;
    var texteRecherche = $('#recherche_textuelle').val();

    if (texteRecherche.length > 0)
    {
        texteRecherche += ' ';
        positionInitiale++;
    }
    texteRecherche += chaineRechercheAvancee;

    $('#recherche_textuelle').val(texteRecherche);
    
    document.getElementById('recherche_textuelle').focus();
    document.getElementById('recherche_textuelle').setSelectionRange(positionInitiale + texteSelectionDebut, positionInitiale + chaineRechercheAvancee.length + texteSelectionFin);
}

function isEmpty( el )
{
    return !$.trim(el.html())
}

//Plugin de highlight
jQuery.extend({
    highlight: function (node, re, nodeName, className) {
        if (node.nodeType === 3) {
            var match = node.data.match(re);
            if (match) {
                var highlight = document.createElement(nodeName || 'span');
                highlight.className = className || 'highlight';
                var wordNode = node.splitText(match.index);
                wordNode.splitText(match[0].length);
                var wordClone = wordNode.cloneNode(true);
                highlight.appendChild(wordClone);
                wordNode.parentNode.replaceChild(highlight, wordNode);
                return 1; //skip added node in parent
            }
        } else if ((node.nodeType === 1 && node.childNodes) && // only element nodes that have children
                !/(script|style)/i.test(node.tagName) && // ignore script and style nodes
                !(node.tagName === nodeName.toUpperCase() && node.className === className)) { // skip if already highlighted
            for (var i = 0; i < node.childNodes.length; i++) {
                i += jQuery.highlight(node.childNodes[i], re, nodeName, className);
            }
        }
        return 0;
    }
});

jQuery.fn.unhighlight = function (options) {
    var settings = { className: 'highlight', element: 'span' };
    jQuery.extend(settings, options);

    return this.find(settings.element + "." + settings.className).each(function () {
        var parent = this.parentNode;
        parent.replaceChild(this.firstChild, this);
        parent.normalize();
    }).end();
};

jQuery.fn.highlight = function (words, options) {
    var settings = { className: 'highlight', element: 'span', caseSensitive: false, wordsOnly: false };
    jQuery.extend(settings, options);
    
    if (words.constructor === String) {
        words = [words];
    }
    words = jQuery.grep(words, function(word, i){
      return word != '';
    });
    words = jQuery.map(words, function(word, i) {
      return word.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
    });
    if (words.length == 0) { return this; };

    var flag = settings.caseSensitive ? "" : "i";
    var pattern = "(" + words.join("|") + ")";
    if (settings.wordsOnly) {
        pattern = "\\ " + pattern + "\\ ";
    }
    var re = new RegExp(pattern, flag);
    
    return this.each(function () {
        jQuery.highlight(this, re, settings.element, settings.className);
    });
};
