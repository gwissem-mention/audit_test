(function($) {
    $.fn.nSelect = function(params) {
        var options = {
            useCheckAll             : true,
            textall                 : "- Tout sélectionner -",
            classSelect             : "nSelect",
            classSelectAddAll       : "nSelectAddAll",
            classSelectClear        : "nSelectClear",
            useWhenMoreThanXOptions : 1
        };
        var opts   = $.extend(options, params);
        var select = new selectNodevo($(this), opts);
        select.init();
        return select;
    }
})(jQuery);

var selectNodevo = function(selector, options) {
    this.selector = selector;
    this.options  = options;
}

selectNodevo.prototype = {
    init: function() {
        if (!this.options.useCheckAll) {
            this.selector.select2(this.options);
        } else {
            this.selector.addClass(this.options.classSelect);
            if( this.selector.find('option').length > this.options.useWhenMoreThanXOptions ){
                var all = $('<option>').val(0).html(this.options.textall).addClass(this.options.classSelectAddAll);
                this.selector.prepend(all);
            }
            var that = this;
            // bouton fermer
            var close = generetaCloseButton(this.options.classSelectClear, that);
            this.selector.select2(this.options);
            this.selector.prev('.select2-container').append(close);
            this.selector.click(function() {
                // on vérifie que la valeur n'est pas vide pour afficher la croix
                if ($(this).find('option:selected').val()) {
                    close.show();
                } else { // Sinon on affiche pas la croix 
                    close.hide();
                }
            }).change(function() {
                var that2 = this;

                if (that.selector.find('option:selected').not('.' + that.options.classSelectAddAll))
                    // si il n'y a pas de valeur alors on l'ajoute
                    if ($(this).find('.' + that.options.classSelectAddAll).length <= 0 && 
                        that.selector.find('option').length > that.options.useWhenMoreThanXOptions ){
                        var newall = $('<option>').val(0).html(that.options.textall).addClass(that.options.classSelectAddAll);
                        $(this).prepend(newall);
                    }

                $(this).find("option:selected").each(function(key, elem) {
                    if ($(elem).hasClass(that.options.classSelectAddAll)) {
                        // on détache l'option
                        $(that2).find('.' + that.options.classSelectAddAll).detach();
                        
                        // on affiche tous les éléments de la liste 
                        $(that2).select2('destroy').find('option').prop('selected', 'selected').end().select2(that.options);
                        close = generetaCloseButton(that.options.classSelectClear, that);
                        $(that2).prev('.select2-container').append(close);
                        
                        // on affiche la croix
                        close.show();
                    }
                });

                if (that.selector.find('option:selected').not('.' + that.options.classSelectAddAll).length ==
                        that.selector.find('option').not('.' + that.options.classSelectAddAll).length) {
                    if (that.selector.find('option.' + that.options.classSelectAddAll).length > 0) {
                        $(that2).find('.' + that.options.classSelectAddAll).detach();
                    }
                }
            });
        }
    }
}

function generetaCloseButton(classe, that){
    var close = $('<input>').attr({
        "type": "button"
    }).addClass(classe).click(function() {
        // on ne sélectionne plus rien
        that.selector.select2("val", "");
        // on n'affiche plus la croix
        $(this).hide();

        // si l'option tout sélectionner n'est pas rpésente
        if (that.selector.find('.' + that.options.classSelectAddAll).length <= 0 && 
            that.selector.find('option').length > that.options.useWhenMoreThanXOptions)
        {
            // on la rajoute
            var newall = $('<option>').val(0).html(that.options.textall).addClass(that.options.classSelectAddAll);
            that.selector.prepend(newall);
        }
    });
    return close;
}