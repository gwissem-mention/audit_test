$(document).ready(function() {
    $('.panel-heading').click(function(){
    	if ($(this).hasClass('open') || $(this).hasClass('closed'))
    	{
    		$(this).toggleClass('open closed');
    	}
        $(this).parent().find('.panel-body').slideToggle();
    });
    $('.panel-parent').click(function(){
    	if ($(this).hasClass('open') || $(this).hasClass('closed'))
    	{
    		$(this).toggleClass('open closed');
    	}
        $(this).parent().find('.panel-children').slideToggle();
    });
});
