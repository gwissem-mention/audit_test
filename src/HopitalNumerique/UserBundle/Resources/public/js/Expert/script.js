$(document).ready(function() { 
	//pop-in affichage du message de refus
    $('#refusCandidature').fancybox({
        'padding'   : 0,
        'autoSize'  : false,
        'width'     : '70%',
        'scrolling' : 'no',
        'modal'     : true
    });
});

function deleteAllReponses(path)
{
    apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if(r) { 
        	customAjaxRedirection(path);
        }
    });
}

function validerCandidature(path)
{
    apprise('Valider la demande de candidature ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if(r) { 
        	customAjaxRedirection(path);
        }
    });
}

function refuserCandidature()
{
    $.ajax({
        url  : $('#refus-candidature-url').val(),
        data : {
            routeRedirection : $('#questionnaire_route_redirection').val(),
            texteRefus       : $('#message-refus').val()
        },
        type     : 'POST',
        dataType : 'json',
        success  : function( data ){
            window.location = data.url;
        }
    });
}

function customAjaxRedirection(path)
{    
    $.ajax({
        url      : path,
        data     : {
        	routeRedirection : $('#questionnaire_route_redirection').val()
        },
        type     : 'POST',
        dataType : 'json',
        success : function( data ){
            window.location = data.url;
        }
    });
}

//function checkTypeFile()
//{		
//	  var validation = true;
//	
//      //Récupération de tout les champs de type file
//      var files = $('input[type="file"]');
//      //tableau des extensions autorisées
//      var exts = ['pdf'];
//      
//      for (var key in files)
//      {
//    	  var file = files[key];
//    	  var lib  = file.value;
//    	  if('' == lib || null == lib) 
//    		  continue;
//    	  
//    	  //Récupère l'extention
//          var get_ext = lib.split('.');
//          get_ext = get_ext.reverse();
//          var extValide = $.inArray ( get_ext[0].toLowerCase(), exts ) > -1;
//          if ( !extValide )
//          {
//        	  alert(lib + ' n\' est pas un pdf !');
//        	  validation =false;
//          }
//      }
//      
//      if( validation )
//    	  $('form').submit();
//      
//}