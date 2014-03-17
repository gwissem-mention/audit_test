function printRef(){
    $('#page-leftbar').hide();
    $('#page-content').css({
        "margin-left" : "0px",
        "box-shadow" : "none"
    });
    window.print();
    $('#page-leftbar #sidebar').css("width", "230px");
    $('#page-leftbar').show();
    $('#page-content').css({
        "margin-left" : "230px",
        "box-shadow" : "-1px 0 0 0 rgba(0,0,0,.1)"
    });
}