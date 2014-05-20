$(document).ready(function() {
    //Load Wizards
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

    //Add Wizard Compability - see docs
    $('.stepy-navigator').wrapInner('<div class="pull-right"></div>');
});