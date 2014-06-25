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

    //add remarques input toggle
    $('.remarques-toggle').click(function(){
        $('#'+$(this).data('target')).slideToggle();
    });

    //init avancement
    calcAvancement();

    //calcul avancement on each answer by type
    $('#wizard fieldset select').on('change', function(){
        calcAvancement();
    });
    $('#wizard fieldset input.question').on('blur', function(){
        calcAvancement();
    });
    $('#wizard fieldset .radios input').on('click', function(){
        calcAvancement();
    });

    //remove les title des fieldset pour empecher l'effet bizarre
    $('fieldset').each(function(){
        $(this).attr('title', '');
    });

    if( $('.badge').length > 0 ){
        $('.badge').qtip({ 
            style : 'qtip-tipsy'
        });
    }
});

//Truncate l'avancement affiché
function truncate(n)
{
    return Math[n > 0 ? "floor" : "ceil"](n);
}

//calcul l'avancement/le remplissage du questionnaire
function calcAvancement()
{
    totalQuestions         = 0;
    totalQuestionsAnswered = 0;

    $('#wizard fieldset').each(function(){
        //get some values
        step                = $(this).attr('id').replace('wizard-step-', '');
        nbQuestions         = 0;
        nbQuestionsAnswered = 0;

        //parcours des questions
        $(this).find('.form-group').each(function(){
            //select
            if( $(this).find('select').length != 0 && $(this).find('select').val() != "" )
                nbQuestionsAnswered++;
            //radio
            else if ( $(this).find('.radios').length != 0 ){ 
                $(this).find('.radios input').each(function(){
                    if( $(this).prop('checked') && $(this).val() != "" ){
                        nbQuestionsAnswered++;
                        return false;
                    }
                });
            //text
            }else if( $(this).find('input.question').length != 0 && $(this).find('input.question').val() != '' )
                nbQuestionsAnswered++;

            nbQuestions++;
        });

        avancement = nbQuestions > 0 ? truncate((nbQuestionsAnswered * 100) / nbQuestions) : 0;

        //update liste
        avancement = avancement < 100 ? '<span class="text-muted">'+avancement+'%</span>' : '<span class="text-success"><i class="fa fa-check"></i></span>';
        $('#wizard-header li#wizard-head-'+step+' div span').remove();
        $('#wizard-header li#wizard-head-'+step+' div').prepend( avancement );

        totalQuestions += nbQuestions;
        totalQuestionsAnswered += nbQuestionsAnswered;
    });

    avancementTotal = totalQuestions > 0 ? truncate((totalQuestionsAnswered * 100) / totalQuestions) : 0;
    $('#autodiag .progress-bar').css('width', avancementTotal + '%');
    $('#autodiag #remplissage').val( avancementTotal );
}

//enregistre ou valide le questionnaire
function saveQuestionnaire( type, userConnected )
{
    $('#action').val( type );

    if( type == 'valid' && userConnected ){
        apprise('La validation de l\'autodiagnostic entraine une historisation de vos résultats et une ré-initialisation de celui-ci.', {'verify':true,'textYes':'Oui','textNo':'Non'}, function(r) {
            if(r) { 
                $('#wizard').submit();
            }
        });
    }else
        $('#wizard').submit();
}