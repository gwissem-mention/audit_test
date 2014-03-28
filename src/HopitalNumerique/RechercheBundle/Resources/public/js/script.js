var DELAY = 200, clicks = 0, timer = null, showPlaceholder = true;

$(document).ready(function() {
    //Gestion de l'ajout de critères dans la requete
    $('#origin li span').on("click", function(e){
        clicks++; //count clicks

        //collapse click
        if(clicks === 1) {
            that  = this;
            timer = setTimeout(function() {
                $(that).parent().toggleClass("active");
                $(that).parent().find('i').toggleClass("fa-chevron-down fa-chevron-right");
                $(that).parent().find('ol:first').slideToggle({duration: 200});

                clicks = 0; //after action performed, reset counter
            }, DELAY);
        //add click
        } else {
            clearTimeout(timer); //prevent single-click action
            success = selectElement( $(this).parent() ); //add element to DEST
            //placeholder management
            if( success && showPlaceholder){
                $(".placeholder").hide();
                showPlaceholder = false;
                $("#dest").removeClass('hide');
                $(".requete h2").addClass('ropen');
            }

            //remove Cookie after each Ref Added/Removed
            $.removeCookie('showMorePointsDurs', { path: '/' });
            $.removeCookie('showMoreProductions', { path: '/' });
            
            if( !$(this).parent().hasClass('level0') )
                updateResultats( false );

            clicks = 0; //after action performed, reset counter
        }
    })
    .on("dblclick", function(e){
        e.preventDefault(); //cancel system double-click event
    });

    //Gestion de la suppression de critères dans la requete
    $('.arbo-requete span').on("click", function(e){
        removeElement( $(this).parent() ); //remove element from DEST

        //remove Cookie after each Ref Added/Removed
        $.removeCookie('showMorePointsDurs', { path: '/' });
        $.removeCookie('showMoreProductions', { path: '/' });
            
        updateResultats( false );
    });

    //toggle des paramètres de la requete
    $('.requete h2').on('click', function(){
        if( $(this).hasClass('ropen') || $(this).hasClass('rclose') ) {
            $(this).toggleClass('ropen rclose');
            $('#dest').slideToggle({duration: 200});
        }
    });

    //init : open first categ
    $('#origin li.level0:first').addClass('active').find('ol:first').slideDown();

    //fancybox daffichage de la synthese
    $('a.synthese').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'scrolling' : 'no'
    });

    if( $('#requete-refs').val() != '[]')
        handleRequestForRecherche();
});

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
}

/**
 * On sélectionne un critère que l'on veut ajouter dans la requete
 */
function selectElement( item )
{
    if( $(item).hasClass('cliquable') ) {
        //cache l'élément de l'origine
        $(item).slideUp().removeClass('cliquable');
        $(item).find('li').slideUp().removeClass('cliquable active'); //pour éviter les bugs, on retire la class cliquable|active à tous les enfants de l'élément

        //cache le parent de l'élément d'origine (si c'est le dernier enfant que l'on viens de cacher)
        handleParentsOrigin( $(item) );
        
        //vérification et ajoute de l'item empty pour le premier level
        $('#origin li.level0').each(function(){
            if ( $(this).find('ol > li.cliquable').length == 0 && $(this).find('ol > li.empty').length == 0 )
                $(this).find('ol').prepend('<li class="empty level1" ><span>Tous les éléments sont selectionnés</span></li>');
        })

        //affiche l'élément dans la liste de droite
        showItemDestRecursive( $(item) );

        //si c'est un parent, on show ces enfants (NON recursif)
        $('#dest .element-' + $(item).data('id') + ' li.hide').removeClass('hide');

    }else
        return false;

    return true;
}

/**
 * On cache en mode récursif tous les parents de l'élément
 */
function handleParentsOrigin( item )
{
    //si le parent de l'élément n'a plus d'enfants cliquables
    if( $(item).parent().find('li.cliquable').length == 0 ) {

        //alors on check si le LI de la liste parente n'est pas le level0
        if( $(item).parent().parent().hasClass('cliquable') ){

            //on remove le LI qui contient la liste des enfants
            $(item).parent().parent().slideUp().removeClass('cliquable');

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

    if ( $(destItem).parent().parent().hasClass('hide') )
        showItemDestRecursive( $(destItem).parent().parent() );
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

        $('#origin .element-' + $(item).data('id') + ' li').slideDown().addClass('cliquable');  //=> jaffiche tous mes enfants
    }
    
    //vérification et retrait de l'item empty pour le premier level
    $('#origin li.level0').each(function(){
        if ( $(this).find('li.cliquable.level1').length > 0)
            $(this).find('li.empty').remove();
    });
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
        }else{
            $(".arbo-requete").find('li').addClass('hide');
            $(".placeholder").show();
            showPlaceholder = true;
            $("#dest").addClass('hide');
            $(".requete h2").removeClass('ropen rclose');
        }
    }
}

/**
 * Affiche les items dans la liste d'origine de manière récursive
 */
function showItemOriginRecursive( item )
{
    originItem = $('#origin .element-' + $(item).data('id') );
    $(originItem).slideDown().addClass('cliquable');
    
    //si mon parent n'est pas affiché, on l'affiche en mode récursif
    if ( !$(originItem).parent().parent().hasClass('cliquable') && !$(originItem).parent().parent().hasClass("level0") )
        showItemOriginRecursive( $(originItem).parent().parent() );
}

/**
 * Met à jour les résulats trouvés en fonction des paramètres de la requête
 */
function updateResultats( cleanSession )
{
    var loader = $('#resultats').nodevoLoader().start();
    
    //AJAX call for results
    $.ajax({
        url  : $('#resultats-url').val(),
        data : {
            references   : getReferences(),
            cleanSession : cleanSession
        },
        type    : 'POST',
        success : function( data ){
            $('#resultats').html( data );
            if( $('#nbResults').val() == 0){
                $('.requete h2').html( 'Requête de recherche' );
                $('#resultats').html('');
            }else if( $('#nbResults').val() == 1 ){
                $('.requete h2').html( 'Requête de recherche ('+$('#nbResults').val()+' Résultat)' );
            }else
                $('.requete h2').html( 'Requête de recherche ('+$('#nbResults').val()+' Résultats)' );

            loader.finished();
        }
    });
}

/**
 * Gestion du bouton Plus de résultats
 */
function showMore(that, btn)
{
    toHide       = 2;
    elementsLeft = 0;
    cookieName   = (btn == 1) ? 'showMorePointsDurs' : 'showMoreProductions';

    //set Default value if not exist
    if( $.cookie(cookieName) == undefined )
        $.cookie(cookieName, 2, {path: '/' });

    //get cookie val
    showMoreCookieVal = $.cookie(cookieName);

    $(that).parent().find('.results > div:hidden').each(function(){
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
        $(that).remove();
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
            nom        : r,
            id         : id,
            references : getReferences()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            if(data.success){
                $('.requeteNom').html( data.nom ).slideDown();
                $('.requeteNom').data('id', data.id);
                if( data.add ){
                    if( data.def == '1' )
                        selected = 'violet';
                    else
                        selected = 'fa-inverse';
                    $('#mesrequetes ul').append('<li><a href="'+data.path+'" ><i class="fa fa-circle '+selected+'"></i>'+data.nom+'</a></li>');
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
    $('.arbo-requete li').each( function(){
        removeElement( $(this) );
    });

    $.removeCookie('showMorePointsDurs', { path: '/' });
    $.removeCookie('showMoreProductions', { path: '/' });


    updateResultats( true );
}