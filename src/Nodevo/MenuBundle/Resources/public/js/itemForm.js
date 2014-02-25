/**
 * Classe gérant le formulaire d'édition d'un item du NodevoMenuBundle.
 * 
 * @author Gaëtan Melchilsen <gmelchilsen@nodevo.com>
 */

/**
 * Initialisation du formulaire.
 */
$(document).ready(function() {

	// -- Bouton selection icon - Glyphicon --
    $('#nodevo_menu_item_buttonIconGlyph').iconpicker({ 
        iconset: 'glyphicon',
        //icon: 'fa-github'
    });

	// -- Bouton selection icon - Fontawesome --
    $('#nodevo_menu_item_buttonIconFontAwesome').iconpicker({ 
        iconset: 'fontawesome',
        //icon: 'fa-github'
    });

	$("#nodevo_menu_item_buttonIconFontAwesome").hide();
	$("#nodevo_menu_item_buttonIconGlyph").hide();

    //récupération du nom de l'icone
    $('#nodevo_menu_item_buttonIconGlyph').on('change', function(e) { 
    	$("#nodevo_menu_item_icon").val($("#nodevo_menu_item_selectIcon").val() + ' ' + e.icon);
    });
    //récupération du nom de l'icone
    $('#nodevo_menu_item_buttonIconFontAwesome').on('change', function(e) { 
    	$("#nodevo_menu_item_icon").val($("#nodevo_menu_item_selectIcon").val() + ' ' + e.icon);
    });

    //Debug
    $('#nodevo_menu_item_selectIcon').on('change', function(e) { 
    	
    	if('glyphicon' === $(this).val())
		{
    		$("#nodevo_menu_item_buttonIconGlyph").show();
    		$("#nodevo_menu_item_buttonIconFontAwesome").hide();
		}
    	else if('fa' === $(this).val())
    	{
    		$("#nodevo_menu_item_buttonIconFontAwesome").show();   
    		$("#nodevo_menu_item_buttonIconGlyph").hide();    		
    	}
    	else
    	{  
    		$("#nodevo_menu_item_buttonIconGlyph").hide(); 
    		$("#nodevo_menu_item_buttonIconFontAwesome").hide(); 
        	$("#nodevo_menu_item_icon").val('');   	
    	}
    	
    });
});


