$(document).ready(function() { 
	
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

function refuserCandidature(path)
{
	apprise('Entrez le motif de refus de la candidature', {'input':true,'textOk':'Envoyer refus','textCancel':'Annuler'}, function(r) {
        if( r ){
        	$.ajax({
                url      : path,
                data     : {
                	routeRedirection : $('#questionnaire_route_redirection').val(),
                	texteRefus : r
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