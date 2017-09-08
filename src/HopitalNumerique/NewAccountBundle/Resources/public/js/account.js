$(function() {
    var currentPage = $('#current-page').data('page');

    $('#my-account #' + currentPage).parents('li').addClass('active');
});
