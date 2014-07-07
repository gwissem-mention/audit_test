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

/**
 * [number_format description]
 *
 * @param  {[type]} number        [description]
 * @param  {[type]} decimals      [description]
 * @param  {[type]} dec_point     [description]
 * @param  {[type]} thousands_sep [description]
 *
 * @return {[type]}
 */
function number_format(number, decimals, dec_point, thousands_sep)
{
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n    = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep  = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec  = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s    = '',
        toFixedFix = function(n, prec) {
          var k = Math.pow(10, prec);
          return '' + (Math.round(n * k) / k)
            .toFixed(prec);
        };

    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3)
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    
    return s.join(dec);
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

        avancement = nbQuestions > 0 ? number_format((nbQuestionsAnswered * 100) / nbQuestions, 0) : 0;

        //update liste
        avancement = avancement < 100 ? '<span class="text-muted">'+avancement+'%</span>' : '<span class="text-success"><i class="fa fa-check"></i></span>';
        $('#wizard-header li#wizard-head-'+step+' div span').remove();
        $('#wizard-header li#wizard-head-'+step+' div').prepend( avancement );

        totalQuestions += nbQuestions;
        totalQuestionsAnswered += nbQuestionsAnswered;
    });

    avancementTotal = totalQuestions > 0 ? number_format((totalQuestionsAnswered * 100) / totalQuestions, 0) : 0;
    $('#autodiag .progress-bar').css('width', avancementTotal + '%');
    $('#autodiag .progress-bar').html(avancementTotal + '%');
    $('#autodiag .progress-bar').attr('aria-valuenow', avancementTotal);
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

//Vide le questionnaire
function emptyAutodiag()
{
    $('#wizard fieldset .emptyChapter').each(function(){
        $(this).click();
    });
}

//vide le chapitre
function emptyChapter( that )
{
    //empty select + inputs
    $(that).parent().find('.form-control').each(function(){
        if( $(this).is('input') )
            $(this).val('');
        else if( $(this).is('select') )
            $(this).val( $(this).find('option:first').val() );
    });

    //empty radios
    $(that).parent().find('.radio').each(function(){
        $(this).find('input').prop('checked', '');
    });

    calcAvancement();
}