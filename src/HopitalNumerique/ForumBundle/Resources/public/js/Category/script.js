//Coche toutes les checkbox
function selectionnerAllRolesCreateCategory()
{
    $("#Forum_CategoryCreate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", true)
    });
}

//Decoche toutes les checkbox
function deselectionnerAllRolesCreateCategory()
{
    $("#Forum_CategoryCreate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", false)
    });
}

//Coche toutes les checkbox
function selectionnerAllRolesEditCategory()
{
    $("#Forum_CategoryUpdate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", true)
    });
}

//Decoche toutes les checkbox
function deselectionnerAllRolesEditCategory()
{
    $("#Forum_CategoryUpdate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", false)
    });
}