$(document).ready(function() {

    $('.deleteAllInfradocs').on('click', function(e) {
        var $this = $(this);
        e.preventDefault();
        return apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
            if (r) {
                window.location.href = $this.attr('href');
            }
        })
    });

    if (window['wordConverter'] !== undefined) {
        wordConverter.onPrepareFormLoaded(function() {
            $('.summary').hide();
            $('.manualAction').hide();
        });

        wordConverter.onAbortion(function() {
            $('.manualAction').show();
            $('.summary').show();
        });
    }

    if( $('#sommaire ol li').length > 0){
        $('#converter-upload-wrapper').hide();
    }

    //gestion du bouton delete : changement du fichier uploadé
    $('.deleteUploadedFile').on('click',function(){
        $(this).hide();
        $(this).parent().find('.uploadedFile').hide();
        $(this).parent().find('.inputUpload').show();
        $('#' + $(this).data('path') ) .val('');
    });

    //gestion du nom de fichier unique
    $('#hopitalnumerique_objet_objet_file, #hopitalnumerique_objet_objet_file2, #hopitalnumerique_objet_objet_fileEdit').on('change', function() {
        $.ajax({
            url  : $('#objet-file-url').val(),
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

    //Ajoute un bloc de contenu au sommaire
    $('.addContenu').click(function(){
        $.ajax({
            url  : $('#add-contenu-url').val(),
            data : {
                key : $('#objet-id').val()
            },
            type     : 'POST',
            success  : function( data ){
                if( data != '' ){
                    $('#sommaire ol:first').append( data );

                    //affiche le lien d'upload CSV + la phrase en cas de données vide
                    if( $('#sommaire ol li').length > 0){
                        $('.uploadSommaire').hide();
                        $('.deleteAllInfradocs').removeClass('hidden');
                        $('.designForBlank').hide();
                        $('#converter-upload-wrapper').hide();

                        if (window['wordConverter'] !== undefined) {
                            window['wordConverter'].unloadUploadForm();
                        }
                    }
                }
                else
                    apprise('Une erreur est survenue lors de l\'ajout de votre contenu, merci de réessayer');
            }
        });
    });

    //Création et gestion de l'arborescence du sommaire
    $('#sommaire').nestable({'maxDepth':10,'group':0}).on('change', function() {
        var serializedDatas = $(this).nestable('serialize');

        $.ajax({
            url  : $('#reorder-objet-url').val(),
            data : {
                datas : serializedDatas
            },
            type     : 'POST',
            dataType : 'json',
            success  : function( data ){

            }
        });
    });

    //Création et gestion de l'arborescence des productions liées
    $('#productions-nestable').nestable({'maxDepth':1,'group':0}).on('change', function() {
        var serializedDatas = $(this).nestable('serialize');

        $.ajax({
            url  : $('#reorder-prods-url').val(),
            data : {
                datas : serializedDatas
            },
            type     : 'POST',
            dataType : 'json',
            success  : function( data ){

            }
        });
    });

    $('#boards-nestable').nestable({'maxDepth':1,'group':0}).on('change', function() {
        var data = $(this).nestable('serialize');

        $.ajax({
            url  : $('#reorder-boards-url').val(),
            data : {
                boards : data
            },
            type     : 'POST',
            dataType : 'json',
            success  : function(data) {

            }
        });
    });

    //fancybox d'édition d'un contenu
    //fancybox de gestion des références liées à l'objet et au contenu
    $('.dd3-content a, .uploadSommaire, .addLink').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'scrolling' : 'auto',
        'modal'     : true
    });

    //recharge le sommaire (et donc la page) on affiche un loader
    $('.reloadContenu').on('click',function(){
        var loader = $('body').nodevoLoader().start();
    })

    //Toggle notif mise à jour
    $('.toggle').toggles( { on : false, text : { on : 'OUI', off : 'NON' } } ).on('toggle', function (e, active) {
        if (active) {
            $('#hopitalnumerique_objet_objet_modified').val(1);
        } else {
            $('#hopitalnumerique_objet_objet_modified').val(0);
        }
    });

    //Toogle d'ajout seulement
    type = !$('#hopitalnumerique_objet_objet_article').val();
    $('.toggleType').toggles( { on : type, width:80, text : { on : 'Objet', off : 'Article' } } ).on('toggle', function (e, active) {
        if (active) { //type = objet
            window.location = $('#objet-addobjet-url').val();
        } else { //type = article
            window.location = $('#objet-addarticle-url').val();
        }
    });

    //reprise du select2 avec le plugin nodevo : sélectionner tout
    $("#hopitalnumerique_objet_objet_roles").nSelect({
        formatNoMatches : function(){ return 'Aucune donnée trouvée'; }
    });

    //bind de Validation Engine
    $('form.toValidate').validationEngine();

    // Keeps current tab active when reloading page
    $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    });
    var activeTab = localStorage.getItem('activeTab');
    if(activeTab){
        $('#object-tab').find('a[href="' + activeTab + '"]').tab('show');
    }
});

$(window).load(function(){
    if( $('#toRef').val() != "0" ){
        $('.open-popin-referencement.edit').delay(800).click();
    }

    $(".form-contenu").each(function(){
        $(this).hide();
    });
});


//Selectionne un chapitre et charge l'ensemble des questions liés
function selectChapitre( id, url )
{
    $('#edition-infradox .selectionInfradoc').hide();
    var loader = $('#edition-infradox').nodevoLoader().start();

    $.ajax({
        url     : url,
        type    : 'POST',
        success : function( data ){
            $('#edition-infradox .results').addClass('well well-lg').html( data );
            $('#edition-infradox .infradoc').val( id );
            loader.finished();
            $('.select2').select2();
            fillRelatedProductionsList(url);
        }
    });
}

function fillRelatedProductionsList(url) {
    var loader = $('.related-productions').nodevoLoader().start();
    var relatedProdList = $('#hopitalnumerique_objet_contenu_objets');
    var preselectedValues = $.parseJSON(relatedProdList.attr('data-preselected-values'));

    $.ajax({
        url: url + '/related-productions',
        type: 'POST',
        dataType: 'json',
        success: function (json) {
            $.each(json, function(index, value) {
                if (preselectedValues.indexOf(index) != -1) {
                    relatedProdList.append('<option value="'+ index +'" selected="selected">'+ value +'</option>');
                } else {
                    relatedProdList.append('<option value="'+ index +'">'+ value +'</option>');
                }
            });
            relatedProdList.select2("destroy");

            relatedProdList.select2();
            loader.finished();
        }
    });
}

//met un loader sur le formulaire et sauvegarde automatiquement le formulaire objet
function saveAutomatique()
{
    var loader = $('body').nodevoLoader().start();
    apprise('Sauvegarde automatique en cours');
    $('#do').val('save-auto');
    $('form').submit();
}

//Enregistre le contenu de la fancybox
function saveContenu()
{
    idContenu = $('#contenu-id').val();
    treeItem = "#tree-item-" + idContenu;
    itemOrder = $(treeItem).data('order');
    var loader = $('#edition-infradox').nodevoLoader().start();

    $.ajax({
        url  : $('#save-contenu-url').val(),
        data : {
            id       : idContenu,
            titre    : $('#hopitalnumerique_objet_contenu_titre').val(),
            alias    : $('#hopitalnumerique_objet_contenu_alias').val(),
            notify   : $('#hopitalnumerique_objet_contenu_modified').val(),
            contenu  : tinyMCE.get('hopitalnumerique_objet_contenu_contenu').getContent(),
            types    : $('#hopitalnumerique_objet_contenu_types').val(),
            objets   : $('#hopitalnumerique_objet_contenu_objets').val(),
            domaines : $('#hopitalnumerique_objet_contenu_domaines').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            if( data.success ){
                selectChapitre( idContenu, $('#contenu-' + idContenu + ' > .dd3-content a').data('url'));
                $('#contenu-' + idContenu + ' > .dd3-content a').html(itemOrder + ' ' + data.titre);
                console.log(data.contenu);
            }else{
                if(data.alias)
                    $('.errorAlias .help-block p').html('L\'alias doit être unique.');
                else
                    $('.errorAlias .help-block p').html('');

                if(data.titre)
                    $('.errorTitre .help-block p').html('Le titre ne peut être vide.');
                else
                    $('.errorTitre .help-block p').html('');
            }
            loader.finished();
        }
    });
}

//Supprime le contenu en cours de visualisation
function deleteContenu( id, url )
{
    apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if(r) { 
            if( id == undefined )
                idContenu = $('#contenu-id').val();
            else
                idContenu = id;

            if( url == undefined )
                url = $('#delete-contenu-url').val();

            $.ajax({
                url  : url,
                data : {
                    id : idContenu
                },
                type     : 'POST',
                dataType : 'json',
                success  : function( data ){
                    if( data.success ){
                        //correction effectuée : si on a supprimer tous les enfants alors on enlève le petit '-' du parent
                        if( data.childs == 0 ){
                            $('#contenu-' + idContenu).parent().parent().find('button').each(function(){
                                $(this).remove();
                            })
                        }

                        $('#edition-infradox .results').html('').removeClass('well well-lg');
                        $('#edition-infradox .selectionInfradoc').show();

                        //supprime l'élément dans le HTML
                        $('#contenu-' + idContenu).remove();

                        //affiche le lien d'upload CSV + la phrase en cas de données vide
                        if( $('#sommaire ol li').length == 0){
                            $('.uploadSommaire').show();
                            $('.deleteAllInfradocs').addClass('hidden');
                            $('.designForBlank').show();
                            $('#converter-upload-wrapper').show();
                        }
                    }
                }
            });
        }
    });
}

//Upload le contenu CSV et le transforme en sommaire
function uploadContenu()
{
    $.ajax({
        url  : $('#parse-upload-url').val(),
        data : {
            csv : $('#csv').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            window.location = data.url;
        }
    });
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

//Sauvegarde de la liaison Point Dur => objets
function addObjet( id )
{
    $.ajax({
        url  : $('#save-link-url').val(),
        data : {
            idObjet : id,
            objets  : $('#objets-linked').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            window.location = data.url;
        }
    });
}

function addBoard(id)
{
    $.ajax({
        url  : $('#save-board-url').val(),
        data : {
            objectId : id,
            boards  : $('#boards-linked').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            window.location = data.url;
        }
    });
}
