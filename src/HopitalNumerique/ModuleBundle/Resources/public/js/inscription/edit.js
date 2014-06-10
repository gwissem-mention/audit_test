$(document).ready(function() {
    //Select2
    $('#hopitalnumerique_module_addinscription_user').select2({
        placeholder: "Choisissez un utilisateur",
        formatNoMatches : function(){
            return "Aucun résultat trouvé.";
        }
    });
});