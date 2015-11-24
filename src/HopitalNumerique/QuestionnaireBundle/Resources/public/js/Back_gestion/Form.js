$(document).ready(function() {
	if ($('#hopitalnumerique_questionnaire_gestion_questionnaire_occurrenceMultiple').is(':checked')) {
		$('#hopitalnumerique_questionnaire_gestion_questionnaire_lien').val("");
		$('#hopitalnumerique_questionnaire_gestion_questionnaire_lien').prop('disabled', true);
	} 
	$("#hopitalnumerique_questionnaire_gestion_questionnaire_occurrenceMultiple").on( "click", function() {
		if ($('#hopitalnumerique_questionnaire_gestion_questionnaire_occurrenceMultiple').is(':checked')) {
			$('#hopitalnumerique_questionnaire_gestion_questionnaire_lien').val("");
			$('#hopitalnumerique_questionnaire_gestion_questionnaire_lien').prop('disabled', true);
		} else {
			$('#hopitalnumerique_questionnaire_gestion_questionnaire_lien').prop('disabled', false);
		} 
	});
});
/** 
* Gestion du formulaire dans l'admin.
 */
var HopitalNumerique_QuestionnaireBundle_Form = function() {};

/**
 * @var boolean Indique si le questionnaire peut avoir plusieurs occurrences
 */
HopitalNumerique_QuestionnaireBundle_Form.IS_OCCURRENCE_MULTIPLE = false;

/**
 * Soumission du formulaire.
 */
HopitalNumerique_QuestionnaireBundle_Form.submit = function()
{
    if (HopitalNumerique_QuestionnaireBundle_Form.checkOccurrenceMultipleForSubmit())
    {
        $('form').submit();
    }

    return false;
};

/**
 * Soumission du formulaire.
 */
HopitalNumerique_QuestionnaireBundle_Form.checkOccurrenceMultipleForSubmit = function()
{
    var occurrenceMultipleCochee = $('#hopitalnumerique_questionnaire_gestion_questionnaire_occurrenceMultiple').is(':checked');
    
    if (HopitalNumerique_QuestionnaireBundle_Form.IS_OCCURRENCE_MULTIPLE && !occurrenceMultipleCochee)
    {
        return confirm('Attention, seules les réponses de la première occurrence seront conservées.' + "\n" + 'Confirmez-vous l\'enregistrement ?')
    }
    
    
    return true;
};
