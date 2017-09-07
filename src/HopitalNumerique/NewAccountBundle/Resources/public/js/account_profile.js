$(function() {

    Hn_Reference_Referencement_Popin.REDIRECTION_URL = null;

    if (window.location.hash) {
        $('[href='+window.location.hash+']').tab('show');
    } else {
        $('.profile-tab-nav').first().tab('show');
    }

    $('.profile-tab-nav').on('shown.bs.tab', function(event){
        location.hash = event.target.getAttribute('href');
    });

    var tabErrorHandler = new TabErrorHandler();

    $('#save-my-account').on('click', function () {
        tabErrorHandler.checkErrors();
        tabErrorHandler.showFirstErrorTab();
    });

    initSelect2($('select.select2'));

    new AjaxList($('select.ajax-list-select2'));
    new CountyList($('#user_account_region'), $('#user_account_county'));
    new HobbyCollection();

    updateUnknownStructureFields($('#user_account_organization'));
    updateKnownStructureFields($('#user_account_organizationLabel'));

    updateProgressBar();
    updateTabCompletionRate();

    $('.completion').on('input change', function () {
        updateProgressBar();
        updateTabCompletionRate();
    });


    $('.deleteUploadedFile').on('click', function () {
        $('.uploadedFile, .deleteUploadedFile ').hide();
        $('.uploadedFile').html('');
        $('.inputUpload').show();
        $('#user_account_file').val('');
        $('#user_account_path').val('');
    });

    $('#my-profile-form').validationEngine({
        validateNonVisibleFields: true,
        focusFirstField: false
    });


    $('[data-toggle-custom]').click(function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        $('a[href="'+ target +'"]').tab('show');
    });

    document.getElementById('leave-communaute-pratique').addEventListener('click', function (ev) {
        CommunautePratique.desinscrit(function () {
            window.location.reload();
        });
    })
});

function initSelect2($select) {
    $select.select2({ width: '100%' });
}

// ------------------------------------- Process completion rate -----------------------------------------------

function updateTabCompletionRate() {

    $('.profile-tab-nav').each(function(k,e) {
        var targetTab = $($(e).attr('href'));
        var navTab = $(e);

        var tabCompletionRate = getCompletionRate(targetTab);

        if (tabCompletionRate === 100) {
            navTab.find('.tab-completion').html('');
        } else {
            navTab.find('.tab-completion').html('(' + tabCompletionRate + '%)');
        }
    });
}

function updateProgressBar() {
    var completion = getCompletionRate($(".panel-body"));
    var progressBar = $('.progress-bar');
    if (completion > 0 && completion < 33) {
        progressBar.removeClass('progress-bar-warning');
        progressBar.removeClass('progress-bar-success');
        progressBar.addClass('progress-bar-danger');
    } else if (completion >= 33 && completion < 66) {
        progressBar.removeClass('progress-bar-danger');
        progressBar.removeClass('progress-bar-success');
        progressBar.addClass('progress-bar-warning');
    } else if (completion >= 66) {
        progressBar.removeClass('progress-bar-danger');
        progressBar.removeClass('progress-bar-warning');
        progressBar.addClass('progress-bar-success');
    }

    progressBar.width(completion + '%');
    progressBar.html(completion + '%');
}

function getCompletionRate($fields) {
    var filledField = 0;
    var missingField = 0;
    $fields.find('.completion:not(.ignore-completion)').each(function () {
        if ('' === $(this).val() || null === $(this).val()) {
            missingField++;
        } else {
            filledField++;
        }
    });

    if (filledField + missingField > 0) {
        return Math.round(filledField/(filledField+missingField)*100);
    } else {
        return 100;
    }
}

// --------------- Structure fields management --------------------------

$(function() {
    $('#user_account_organization').on('change', function() {
        updateUnknownStructureFields(this);
    });

    $('#user_account_organizationLabel').on('input', function() {
        updateKnownStructureFields(this);
    });
});

function updateKnownStructureFields(updatedField) {
    if ('' === $(updatedField).val() || null === $(updatedField).val()) {
        $('.known-structure').stop().show();
        $('.known-structure .completion').removeClass('ignore-completion');
    } else {
        $('.known-structure').stop().hide();
        $('.known-structure .completion').addClass('ignore-completion');
    }

    updateProgressBar();
    updateTabCompletionRate();
}

function updateUnknownStructureFields(updatedField) {
    if ('' === $(updatedField).val() || null === $(updatedField).val()) {
        $('.unknown-structure').stop().show();
        $('.unknown-structure .completion').removeClass('ignore-completion')
    } else {
        $('.unknown-structure').stop().hide();
        $('.unknown-structure .completion').addClass('ignore-completion')
    }

    updateProgressBar();
    updateTabCompletionRate();
}
