$(document).ready(function() {
    tinymce.PluginManager.load('table', '/bundles/nodevoadmin/plugins/tinymce/plugins/table/plugin.min.js');
    tinymce.PluginManager.load('code', '/bundles/nodevoadmin/plugins/tinymce/plugins/code/plugin.min.js');
    tinymce.PluginManager.load('pagebreak', '/bundles/nodevoadmin/plugins/tinymce/plugins/pagebreak/plugin.min.js');
    tinymce.PluginManager.load('importcss', '/bundles/nodevoadmin/plugins/tinymce/plugins/importcss/plugin.min.js');
    tinymce.PluginManager.load('textcolor', '/bundles/hopitalnumeriqueobjet/js/ObjetTextColor/plugin.min.js');
    tinymce.PluginManager.load('image', '/bundles/nodevoadmin/plugins/tinymce/plugins/image/plugin.min.js');
    tinymce.PluginManager.load('link', '/bundles/nodevoadmin/plugins/tinymce/plugins/link/plugin.min.js');
    NodevoGestionnaireMediaBundle_MoxieManager.initTinyMce();

    //Save auto : toutes les 5 minutes  
    //setInterval(saveAutomatique, 300000);

    tinyMCE.init({
        selector     : "textarea",
        theme        : "modern",
        theme_url    : '/bundles/nodevoadmin/plugins/tinymce/themes/modern/theme.min.js',
        skin_url     : '/bundles/nodevoadmin/plugins/tinymce/skins/lightgray',
        plugins      : 'moxiemanager image table code textcolor pagebreak importcss link',
        height       : 210,
        menubar      : false,
        content_css  : '/bundles/hopitalnumeriqueobjet/css/wysiwyg.css',
        toolbar1     : "code | undo redo cut copy paste | pagebreak | link | insertfile image ",
        toolbar2     : "styleselect | bold italic underline strikethrough subscript superscript blockquote | forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table ",
        style_formats: [
            {title: 'Titres', items: [
                {title: 'Titre 2', block: 'h2'},
                {title: 'Titre 3', block: 'h3'},
                {title: 'Titre 4', block: 'h4'},
                {title: 'Titre 5', block: 'h5'},
                {title: 'Titre 6', block: 'h6'}
            ]}
        ],
        importcss_append: true,
        importcss_groups: [
            {title: 'Styles personnalisés'}
        ],
        relative_urls:false
    });

    //gestion du bouton delete : changement du fichier uploadé
    $('.deleteUploadedFile').on('click',function(){
        $(this).hide();
        $(this).parent().find('.uploadedFile').hide();
        $(this).parent().find('.inputUpload').show();
        $('#' + $(this).data('path') ) .val('');
    });

    //gestion du nom de fichier unique
    $('#hopitalnumerique_objet_objet_file, #hopitalnumerique_objet_objet_file2').on('change', function() {
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
                        $('.designForBlank').hide();
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

    //fancybox d'édition d'un contenu
    //fancybox de gestion des références liées à l'objet et au contenu
    $('.dd3-content a, .manageReferences, .uploadSommaire').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '80%',
        'scrolling' : 'no',
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
});

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

    $.ajax({
        url  : $('#save-contenu-url').val(),
        data : {
            id      : idContenu,
            titre   : $('#hopitalnumerique_objet_contenu_titre').val(),
            alias   : $('#hopitalnumerique_objet_contenu_alias').val(),
            notify  : $('#hopitalnumerique_objet_contenu_modified').val(),
            contenu : tinyMCE.get('hopitalnumerique_objet_contenu_contenu').getContent()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            if( data.success ){
                $.fancybox.close(true);
                $('#contenu-' + idContenu + ' > .dd3-content a').html( data.titre );
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
                        if( id == undefined )
                            $.fancybox.close(true);
                        
                        //correction effectuée : si on a supprimer tous les enfants alors on enlève le petit '-' du parent
                        if( data.childs == 0 ){
                            $('#contenu-' + idContenu).parent().parent().find('button').each(function(){
                                $(this).remove();
                            })
                        }

                        //supprime l'élément dans le HTML
                        $('#contenu-' + idContenu).remove();

                        //affiche le lien d'upload CSV + la phrase en cas de données vide
                        if( $('#sommaire ol li').length == 0){
                            $('.uploadSommaire').show();
                            $('.designForBlank').show();
                        }
                    }
                }
            });
        }
    });
}

//Sauvegarde les références de l'objet et du contenu
function saveReferences( objet, idContenu )
{
    var references = [];
    var loader     = $('.panel-body').nodevoLoader().start();

    $('#references-tab .ref').each(function() {
        //si la référence est cochée, on l'ajoute dans les références à linker
        if ( $(this).find('.checkbox').prop('checked') ) {
            var ref = {};

            //ref id
            ref.id = $(this).data('id');

            //type primary
            ref.type = $(this).find('.toggle-slide').hasClass('active');

            references.push( ref );    
        }
    });

    //JSONify IT !
    json = JSON.stringify( references );

    if( objet )
        ajaxUrl = $('#save-references-objet-url').val();
    else
        ajaxUrl = $('#save-references-contenu-url').val();

    //save the value
    $.ajax({
        url  : ajaxUrl,
        data : {
            references : json,
            contenu    : idContenu
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            //update ref number
            if(objet)
                $('.nbRefs').html( data.nbRef );    
            else
                $('#sommaire #contenu-'+idContenu+' > .dd3-content .text-muted span').html( data.nbRef );
            
            loader.finished();
            $.fancybox.close(true);
        }
    });
}

//checkbox de selection multiple
function checkAllReferences()
{
    if( $('.checkAll').prop('checked') ){
        $('#references-tab .checkbox').each(function(){
            childs = $(this).parent().parent().data('childs');

            if( childs.length > 0 ){
                $.each(childs,function(key, val){
                    $('.ref-'+val+' .checkbox').prop('checked','checked');
                    $('.ref-'+val+' .checkbox').prop('disabled','disabled');
                });
            }

            $(this).prop('checked', 'checked');
        })
    }else{
        $('#references-tab .checkbox').each(function(){
            $(this).prop('checked', false);
            $(this).prop('disabled', '');
        })
    }
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