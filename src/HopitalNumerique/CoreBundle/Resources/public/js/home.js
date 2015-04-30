$(document).ready(function(){
    $('#slide').slick({
        adaptiveHeight:true,
        dots: true,
        autoplay: true,
        infinite:true,
        prevArrow: "<i class='fa fa-chevron-circle-left slick-prev-custom'></i>",
        nextArrow: "<i class='fa fa-chevron-circle-right slick-next-custom'></i>",
        responsive: [
            {
                breakpoint: 550,
                settings: {
                    arrows:false
                }
            }
        ]
    });
    $('.block-home-menu .block-bottom').hover(
            function() {
                $(this).find('p').animate( {'height':"160px", 'display':'block'}, { queue:false, duration:500 });
            },
            function() {
                $(this).find('p').animate( {'height':"0px", 'display':'none'}, { queue:false, duration:500 });
            }
    );


    $("#menu-container").find('ul.menu_level_1').addClass('menu-bottom');

    $('.onbottomdown').click(function() {
        $(this).hide();
        $('.onbottomup').show();
    });

    $('.onbottomup').click(function() {
        $(this).hide();
        $('.onbottomdown').show();
    });

    $('#block-carte-france').hover(
        function() {
            $('.carte-france').stop().fadeOut();
        },
        function() {
            $('.carte-france').stop().fadeIn();
        }
    )
});

$(window).scroll(function() {
    var scrollTop = $(this).scrollTop();
    if(scrollTop <= 100 ) {
        $("#menu-container").find('ul.menu_level_1').addClass('menu-bottom');
    } else {
        $("#menu-container").find('ul.menu_level_1').removeClass('menu-bottom');
    }

    if($(window).width() > 1650) {
        if(scrollTop >= ($(window).height() - $('#menu-container').outerHeight())) {
            $("#menu-container").addClass('menu-sticky');
        } else {
            $("#menu-container").removeClass('menu-sticky');
        }
    }
});