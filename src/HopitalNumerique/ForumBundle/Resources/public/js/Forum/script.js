//Coche toutes les checkbox
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