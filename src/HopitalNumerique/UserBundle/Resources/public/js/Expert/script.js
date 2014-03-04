$(document).ready(function() { 
	
});

function deleteAllReponses(path)
{
    apprise('Attention, cette opération est irréversible, êtes-vous sur de vouloir continuer ?', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
        if(r) { 
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
    });
}

function checkTypeFile()
{
//      var file = $('input[type="file"]').val();
//      var exts = ['pdf'];
//      
//      // first check if file field has any value
//      if ( file ) {
//        // split file name at dot
//        var get_ext = file.split('.');
//        // reverse name to check extension
//        get_ext = get_ext.reverse();
//        // check file type is valid as given in 'exts' array
//        if ( $.inArray ( get_ext[0].toLowerCase(), exts ) > -1 )
//        {
//          alert( 'Allowed extension!' );
//        } 
//        else 
//        {
//        	apprise( 'Veuillez selectionner un pdf' );
//        }
//      }
		
	  var validation = true;
	
      //Récupération de tout les champs de type file
      var files = $('input[type="file"]');
      //tableau des extensions autorisées
      var exts = ['pdf'];
      
      for (var key in files)
      {
    	  var file = files[key];
    	  var lib  = file.value;
    	  if('' == lib || null == lib) 
    		  continue;
    	  
    	  //Récupère l'extention
          var get_ext = lib.split('.');
          get_ext = get_ext.reverse();
          var extValide = $.inArray ( get_ext[0].toLowerCase(), exts ) > -1;
          if ( !extValide )
          {
        	  alert(lib + ' n\' est pas un pdf !');
        	  validation =false;
          }
      }
      
      if( validation )
    	  $('form').submit();
      
}