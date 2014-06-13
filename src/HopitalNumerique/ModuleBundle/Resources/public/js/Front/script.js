$(function() {
    $('a.link').fancybox({
        'padding'   : 0,
        'scrolling' : 'auto',
        'width'     : '70%',
        'height'    : 'auto'
    });
});

//fancybox daffichage
enquire.register("screen and (max-width: 991px)", {
    match : function() {
        $(function() {
            $(document).unbind('click.fb-start');
            $('a.link').attr('target','_blank');
        });
    },
    unmatch : function() {
        $(function() {
            $('a.link').fancybox({
                'padding'   : 0,
                'scrolling' : 'auto',
                'width'     : '70%',
                'height'    : 'auto'
            });
            $('a.link').attr('target','');
        });
    }
});
