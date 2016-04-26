
//Epingle le fil du forum
$(document).ready(function(){
   $("#pinTopic").click(function(){
       $.ajax
       ({
           type: "Post",
           dataType: "json",
           url: $('#pinTopic').attr("href"),
           success: function(result){
               if(result.success){
                   $('#pinTopic').removeClass('btn-warning');
                   $('#pinTopic').addClass('btn-success');
               } else {
                   $('#pinTopic').removeClass('btn-success');
                   $('#pinTopic').addClass('btn-warning');
               }
           },
           error: function(data){
               alert('Erreur lors de l\'enregistrement');
           }
       });
   });
});

// /Coche toutes les checkbox
function selectionnerAllRolesCreateForum()
{
    $("#Forum_ForumCreate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", true)
    });
}

//Decoche toutes les checkbox
function deselectionnerAllRolesCreateForum()
{
    $("#Forum_ForumCreate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", false)
    });
}

//Coche toutes les checkbox
function selectionnerAllRolesEditForum()
{
    $("#Forum_ForumUpdate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", true)
    });
}

//Decoche toutes les checkbox
function deselectionnerAllRolesEditForum()
{
    $("#Forum_ForumUpdate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", false)
    });
}