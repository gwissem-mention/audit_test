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

        initTinyMce();

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
                initTinyMce();
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
            id                   : $('#idQuestion').val(),
            libelle              : $('#libelle_question').val(),
            typeQuestion         : $('#typeQuestion').val(),
            refTypeQuestion      : $('#typeQuestion_reference').val(),
            commentaire_question : tinyMCE.get('commentaire_question').getContent(),
            obligatoire          : $('#questionObligatoire').is(":checked")
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
            initTinyMce();
        }
    });

    $('#reponses .question').val( id );
}

function initTinyMce () {
  tinymce.PluginManager.load('table', '/bundles/nodevotools/js/tinymce/plugins/table/plugin.min.js');
  tinymce.PluginManager.load('code', '/bundles/nodevotools/js/tinymce/plugins/code/plugin.min.js');
  tinymce.PluginManager.load('pagebreak', '/bundles/nodevotools/js/tinymce/plugins/pagebreak/plugin.min.js');
  tinymce.PluginManager.load('importcss', '/bundles/nodevotools/js/tinymce/plugins/importcss/plugin.min.js');
  tinymce.PluginManager.load('image', '/bundles/nodevotools/js/tinymce/plugins/image/plugin.min.js');
  tinymce.PluginManager.load('link', '/bundles/nodevotools/js/tinymce/plugins/link/plugin.min.js');
  tinymce.PluginManager.load('media', '/bundles/nodevotools/js/tinymce/plugins/media/plugin.min.js');
  tinymce.PluginManager.load('textcolor', '/bundles/nodevotools/js/plugins/text-color/plugin.min.js');
  NodevoGestionnaireMediaBundle_MoxieManager.initTinyMce();

  tinyMCE.init({
      entity_encoding : "raw",
      selector        : "textarea.tinyMce",
      theme           : "modern",
      theme_url       : '/bundles/nodevotools/js/tinymce/themes/modern/theme.min.js',
      skin_url        : '/bundles/nodevotools/js/tinymce/skins/lightgray',
      plugins         : 'moxiemanager image table code textcolor pagebreak importcss link',
      height          : 210,
      menubar         : false,
      content_css     : '/bundles/nodevotools/css/wysiwyg.css',
      toolbar1        : "code | undo redo cut copy paste | pagebreak | link ",
      toolbar2        : "styleselect | bold italic underline strikethrough subscript superscript blockquote | forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table ",
      style_formats: [
          {title: 'Titres', items: [
              {title: 'Titre 2', block: 'h2'},
              {title: 'Titre 3', block: 'h3'},
              {title: 'Titre 4', block: 'h4'},
              {title: 'Titre 5', block: 'h5'},
              {title: 'Titre 6', block: 'h6'}
          ]},
          {title: 'Blocs', items: [
              {title: 'Paragraphe', block: 'p'}
          ]},
          {title: 'Tableaux', items: [
              {title: 'Tableau simple', selector: 'table', classes: 'table table-striped table-bordered table-hover'}
          ]}
      ],
      importcss_append: true,
      importcss_groups: [
          {title: 'Styles personnalisés'}
      ],
      relative_urls:false
  });
}
