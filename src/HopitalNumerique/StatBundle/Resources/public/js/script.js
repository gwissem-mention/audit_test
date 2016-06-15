//Script à optimiser

$(document).ready(function() { 

        //bind de Validation Engine
        $('form.toValidate').validationEngine();
        $("#check-url-erreurs-curl").hide();
        $("#check-url-erreurs-curl-with-base").hide();

        //---Etape

        //Date début
        $( "#datepicker-datedebut-rechercheParcours" ).datepicker({
            defaultDate: "now",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datefin-rechercheParcours" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#datepicker-datedebut-rechercheParcours" ).datepicker( "option", "showAnim", "fadeIn" );

        //Date de fin
        $( "#datepicker-datefin-rechercheParcours" ).datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datedebut-rechercheParcours" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
        $( "#datepicker-datefin-rechercheParcours" ).datepicker( "option", "showAnim", "fadeIn" );

        //---Item requete

        //Date début
        $( "#datepicker-datedebut-itemRequete" ).datepicker({
            defaultDate: "now",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datefin-itemRequete" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#datepicker-datedebut-itemRequete" ).datepicker( "option", "showAnim", "fadeIn" );

        //Date de fin
        $( "#datepicker-datefin-itemRequete" ).datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datedebut-itemRequete" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
        $( "#datepicker-datefin-itemRequete" ).datepicker( "option", "showAnim", "fadeIn" );

        //---Requete fantome

        //Date début
        $( "#datepicker-datedebut-requeteFantom" ).datepicker({
            defaultDate: "now",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datefin-requeteFantom" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#datepicker-datedebut-requeteFantom" ).datepicker( "option", "showAnim", "fadeIn" );

        //Date de fin
        $( "#datepicker-datefin-requeteFantom" ).datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datedebut-requeteFantom" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
        $( "#datepicker-datefin-requeteFantom" ).datepicker( "option", "showAnim", "fadeIn" );

        //---Erreurs du curl
        //----Pour autodiag
        //Date début
        $( "#datepicker-datedebut-erreursCurl-autodiag" ).datepicker({
            defaultDate: "now",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datefin-erreursCurl-autodiag" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#datepicker-datedebut-erreursCurl-autodiag" ).datepicker( "option", "showAnim", "fadeIn" );

        //Date de fin
        $( "#datepicker-datefin-erreursCurl-autodiag" ).datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datedebut-erreursCurl-autodiag" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
        $( "#datepicker-datefin-erreursCurl-autodiag" ).datepicker( "option", "showAnim", "fadeIn" );

        //---Stat clic

        //Date début
        $( "#datepicker-datedebut-statClic" ).datepicker({
            defaultDate: "now",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datefin-statClic" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#datepicker-datedebut-statClic" ).datepicker( "option", "showAnim", "fadeIn" );

        //Date de fin
        $( "#datepicker-datefin-statClic" ).datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datedebut-statClic" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
        $( "#datepicker-datefin-statClic" ).datepicker( "option", "showAnim", "fadeIn" );

        //---Date de production

        //Date début
        $( "#datepicker-datedebut-itemProduction" ).datepicker({
            defaultDate: "now",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datefin-itemProduction" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#datepicker-datedebut-itemProduction" ).datepicker( "option", "showAnim", "fadeIn" );

        //Date de fin
        $( "#datepicker-datefin-itemProduction" ).datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datedebut-itemProduction" ).datepicker( "option", "maxDate", selectedDate );
            }
        });

        //---stats des forum

        //Date début
        $( "#datepicker-datedebut-forum" ).datepicker({
            defaultDate: "now",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datefin-forum" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#datepicker-datedebut-forum" ).datepicker( "option", "showAnim", "fadeIn" );

        //Date de fin
        $( "#datepicker-datefin-forum" ).datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: "dd-mm-yy",
            onClose: function( selectedDate ) {
                $( "#datepicker-datedebut-forum" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
        $( "#datepicker-datefin-forum" ).datepicker( "option", "showAnim", "fadeIn" );
    });

    function generationRechercheParcours(url)
    {
        if ( $('#form-recherche-parcours').validationEngine('validate') ) 
        {
            var loaderButton  = $('#generation-tableau-recherche-parcours').nodevoLoader().start();
            var loaderTableau = $('#recherche-parcours-tableau').nodevoLoader().start();

            $.ajax({
                url     : url,
                data    :  $('#form-recherche-parcours').serialize(),
                type    : 'POST',
                success : function( data ){
                    //Ajout de la réponse
                    $('#recherche-parcours-tableau').html( data );
                    loaderButton.finished();
                    loaderTableau.finished();
                }
            });
        }
    }

    function exportCSVRechercheParcours()
    {
        if ( $('#form-recherche-parcours').validationEngine('validate') ) 
        {
            $('#form-recherche-parcours').submit();
        }
    }

    function exportCSVItemRequete()
    {
        if ( $('#form-item-requete').validationEngine('validate') ) 
        {
            $('#form-item-requete').submit();
        }
    }

    function exportCSVForum()
    {
        if ( $('#form-stat-forum').validationEngine('validate') ) 
        {
            $('#form-stat-forum').submit();
        }
    }

    function generationItemRequete(url)
    {
        if ( $('#form-item-requete').validationEngine('validate') ) 
        {
            var loaderButton  = $('#generation-tableau-item-requete').nodevoLoader().start();
            var loaderTableau = $('#item-requete-tableau').nodevoLoader().start();

            $.ajax({
                url     : url,
                data    :  $('#form-item-requete').serialize(),
                type    : 'POST',
                success : function( data ){
                    //Ajout de la réponse
                    $('#item-requete-tableau').html( data );
                    loaderButton.finished();
                    loaderTableau.finished();
                }
            });
        }
    }

    function generationRequeteFantom(url)
    {
        if ( $('#form-requete-fantome').validationEngine('validate') ) 
        {
            var loaderButton  = $('#generation-tableau-requete-fantome').nodevoLoader().start();
            var loaderTableau = $('#requete-fantome-tableau').nodevoLoader().start();

            $.ajax({
                url     : url,
                data    :  $('#form-requete-fantome').serialize(),
                type    : 'POST',
                success : function( data ){
                    //Ajout de la réponse
                    $('#requete-fantome-tableau').html( data );
                    loaderButton.finished();
                    loaderTableau.finished();
                }
            });
        }
    }

    function generationErreursCurl(url)
    {
        if ( $('#form-erreurs-curl').validationEngine('validate') ) 
        {
            var loaderButton  = $('#generation-tableau-erreurs-curl').nodevoLoader().start();
            var loaderTableau = $('#erreurs-curl-tableau').nodevoLoader().start();

            $.ajax({
                url     : url,
                data    :  $('#form-erreurs-curl').serialize(),
                type    : 'POST',
                success : function( data ){
                    //Ajout de la réponse
                    $('#erreurs-curl-tableau').html( data );
                    loaderButton.finished();
                    loaderTableau.finished();
                    $("#check-url-erreurs-curl").show();
                    $("#check-url-erreurs-curl-with-base").show();
                }
            });
        }
    }

    function generationErreursCurlAutodiag(url)
    {
        if ( $('#form-erreurs-curl-autodiag').validationEngine('validate') ) 
        {
            var loaderButton  = $('#generation-tableau-erreurs-curl-autodiag').nodevoLoader().start();
            var loaderTableau = $('#erreurs-curl-autodiag-tableau').nodevoLoader().start();

            $.ajax({
                url     : url,
                data    :  $('#form-erreurs-curl-autodiag').serialize(),
                type    : 'POST',
                success : function( data ){
                    //Ajout de la réponse
                    $('#erreurs-curl-autodiag-tableau').html( data );
                    loaderButton.finished();
                    loaderTableau.finished();
                    $("#check-url-erreurs-curl-autodiag").show();
                    $("#check-url-erreurs-curl-autodiag-with-base").show();
                }
            });
        }
    }

    function checkAllUrl( withBase )
    {
        $("#item-erreurs-curl .url-check").each(function(){

            var item = $(this);

            var url = withBase ? $('#curl-url').val() : $('#curl-url-with-base').val();

            var loaderItemCourant = item.nodevoLoader().start();

            $.ajax({
                url  : url,
                data : {
                    url : $(this).data('url')
                },
                type     : 'POST',
                dataType : 'json',
                success  : function( data ){
                    if( data.success ){
                        item.html('<div class="finish btn btn-success url-check" disabled="disabled"><i class="fa fa-check"></i></div>');
                    }
                    else{
                        item.html('<div class="finish btn btn-danger url-check" disabled="disabled"><i class="fa fa-minus-circle"></i></div>');
                    }
                    loaderItemCourant.finished();
                }
            });
        });
    }

    function exportCSVErreursUrl()
    {
        if ( $('#form-erreurs-curl').validationEngine('validate') ) 
        {
            $('#form-erreurs-curl').submit();
        }
    }

    function exportCSVErreursUrlAutodiag()
    {
        if ( $('#form-erreurs-curl-autodiag').validationEngine('validate') ) 
        {
            $('#form-erreurs-curl-autodiag').submit();
        }
    }

    function exportCSVItemProduction()
    {
        if ( $('#form-item-production').validationEngine('validate') ) 
        {
            $('#form-item-production').submit();
        }
    }

    function generationItemProduction(url)
    {
        if ( $('#form-item-production').validationEngine('validate') ) 
        {
            var loaderButton  = $('#generation-tableau-item-production').nodevoLoader().start();
            var loaderTableau = $('#item-production-tableau').nodevoLoader().start();

            $.ajax({
                url     : url,
                data    :  $('#form-item-production').serialize(),
                type    : 'POST',
                success : function( data ){
                    //Ajout de la réponse
                    $('#item-production-tableau').html( data );
                    loaderButton.finished();
                    loaderTableau.finished();
                }
            });
        }
    }

    function generationStatClic(url)
    {
        if ( $('#form-stat-clic').validationEngine('validate') ) 
        {
            var loaderButton  = $('#generation-tableau-stat-clic').nodevoLoader().start();
            var loaderTableau = $('#stat-clic-tableau').nodevoLoader().start();

            $.ajax({
                url     : url,
                data    :  $('#form-stat-clic').serialize(),
                type    : 'POST',
                success : function( data ){
                    //Ajout de la réponse
                    $('#stat-clic-tableau').html( data );
                    loaderButton.finished();
                    loaderTableau.finished();
                }
            });
        }
    }

    function generationStatForum(url)
    {
        if ( $('#form-stat-forum').validationEngine('validate') ) 
        {
            var loaderButton  = $('#generation-tableau-stat-forum').nodevoLoader().start();
            var loaderTableau = $('#stat-forum-tableau').nodevoLoader().start();

            $.ajax({
                url     : url,
                data    :  $('#form-stat-forum').serialize(),
                type    : 'POST',
                success : function( data ){
                    //Ajout de la réponse
                    $('#stat-forum-tableau').html( data );
                    loaderButton.finished();
                    loaderTableau.finished();
                }
            });
        }
    }


    /**
     * Affiche l'élément en mode récursif
     */
    function showElementRecursive( destItem )
    {
        $(destItem).removeClass('hide');

        if ( $(destItem).parent().parent().hasClass('hide') )
            showElementRecursive( $(destItem).parent().parent() );
    }


    /**
     * Prend en compte la requete par défaut ou la requete active
     */
    function handleRefs()
    {
        $("#item-requete-table .requete-fantome-arbo").each(function(){

            var requeteFantome = $(this);
            var refs = $.parseJSON( requeteFantome.find(".requete-refs").val() );

            $.each(refs, function(key, val){
                $.each(val.reverse(), function( key, item ){
                    element = requeteFantome.find('.arbo-requete .element-'+item);
                    if( $(element).hasClass('hide') ){
                        //affiche l'élément
                        showElementRecursive( $(element) );

                        //si c'est un parent, on show ces enfants (NON recursif)
                        $(element).find('li.hide').removeClass('hide');
                    }
                });
            });
        });
    }