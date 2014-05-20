$(document).ready(function() {
    $('#wizard').stepy({
        finishButton : true, 
        titleClick   : true, 
        description  : false,
        finishButton : false,
        legend       : false,
        block        : true, 
        backLabel    : 'Chapitre précédent',
        nextLabel    : 'Chapitre suivant',
        titleTarget  : '#chapitres'
    });
});