$(document).ready(function() {
    //reprise du select2 avec le plugin nodevo : sélectionner tout
    $("#hopitalnumerique_flash_flash_roles").nSelect({
        formatNoMatches : function(){ return 'Aucune donnée trouvée'; }
    });
});