//Création
//Coche toutes les checkbox
function selectionnerAllRolesReadCreateBoard()
{
    $("#Forum_BoardCreate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", true)
    });
}

//Decoche toutes les checkbox
function deselectionnerAllRolesReadCreateBoard()
{
    $("#Forum_BoardCreate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", false)
    });
}
//Coche toutes les checkbox
function selectionnerAllRolesReplyCreateBoard()
{
    $("#Forum_BoardCreate_topicReplyAuthorisedRoles input").each(function(){
        $(this).prop("checked", true)
    });
}

//Decoche toutes les checkbox
function deselectionnerAllRolesReplyCreateBoard()
{
    $("#Forum_BoardCreate_topicReplyAuthorisedRoles input").each(function(){
        $(this).prop("checked", false)
    });
}
//Coche toutes les checkbox
function selectionnerAllRolesNewCreateBoard()
{
    $("#Forum_BoardCreate_topicCreateAuthorisedRoles input").each(function(){
        $(this).prop("checked", true)
    });
}

//Decoche toutes les checkbox
function deselectionnerAllRolesNewCreateBoard()
{
    $("#Forum_BoardCreate_topicCreateAuthorisedRoles input").each(function(){
        $(this).prop("checked", false)
    });
}

//Edition

//Coche toutes les checkbox
function selectionnerAllRolesReadEditBoard()
{
    $("#Forum_BoardUpdate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", true)
    });
}

//Decoche toutes les checkbox
function deselectionnerAllRolesReadEditBoard()
{
    $("#Forum_BoardUpdate_readAuthorisedRoles input").each(function(){
        $(this).prop("checked", false)
    });
}
//Coche toutes les checkbox
function selectionnerAllRolesReplyEditBoard()
{
    $("#Forum_BoardUpdate_topicReplyAuthorisedRoles input").each(function(){
        $(this).prop("checked", true)
    });
}

//Decoche toutes les checkbox
function deselectionnerAllRolesReplyEditBoard()
{
    $("#Forum_BoardUpdate_topicReplyAuthorisedRoles input").each(function(){
        $(this).prop("checked", false)
    });
}
//Coche toutes les checkbox
function selectionnerAllRolesNewEditBoard()
{
    $("#Forum_BoardUpdate_topicCreateAuthorisedRoles input").each(function(){
        $(this).prop("checked", true)
    });
}

//Decoche toutes les checkbox
function deselectionnerAllRolesNewEditBoard()
{
    $("#Forum_BoardUpdate_topicCreateAuthorisedRoles input").each(function(){
        $(this).prop("checked", false)
    });
}