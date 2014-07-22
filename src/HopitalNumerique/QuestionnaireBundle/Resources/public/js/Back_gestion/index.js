$(document).ready(function() { 
    //Cache les boutons de l'export
    hideButtons();

    $("#questionnaireExport").change(function() {
        hideButtons();
        var idQuestionnaire = $(this).val();
        $('#export-csv-' + idQuestionnaire).show();
    });
});

function hideButtons()
{
    $("#export-csv-buttons div").each(function(){
        $(this).hide();
    })
}