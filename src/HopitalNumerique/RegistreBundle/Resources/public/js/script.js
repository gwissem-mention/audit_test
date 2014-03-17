$(document).ready(function() {
    //fancybox liste des outils maitrisés
    $('a.link').fancybox({
        'padding'   : 0,
        'autoSize'  : true,
        'scrolling' : 'yes'
    });
    
    $('#domaines_liste').change(function(){
        window.location.href = $(this).data('url') + "/" + $(this).val();
    });
});

$(document).bind("carteReady", function(){
    selectedRegion = $('#selected-region').val();

    if ( selectedRegion ){
        $('#canvas_france a').each(function(key, val){
            if( $(this).attr('title') == selectedRegion ){
                $(this).find('path').attr('fill', '#6f3596');
            }
        });
    }
});