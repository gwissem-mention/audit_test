/*
loader = $('#div').nodevoLoader({
    'class' : 'loader',
    'speed' : 'normal',
}).start();

var loader = $('#header #login').nodevoLoader().start();
loader.finished();
*/


// HOW TO USE

(function($) {
    $.fn.nodevoLoader = function ( options ) {
        var optionsD = {
            'cssClass'    : 'loader',
            'speed'       : 'normal',
            'beforeStart' : $.noop,
            'afterStop'   : $.noop
        };

        var opts   = $.extend(optionsD, options);
        var loader = new nodevoLoader( $(this), opts );
        loader.init();
        return loader;
    };
})(jQuery);

function nodevoLoader( elem, options ) {
    this.elem = elem;
    this.opts = options;
    this.dom  = {};

    if ( typeof nodevoLoader.initialized == "undefined" ) {
        nodevoLoader.prototype.init = function() {
            loaderContainer = $('<div />').addClass( this.opts.cssClass );

            loaderBack = $('<div />').addClass('loaderBackground');
            loaderIcon = $('<div />').addClass('loaderIcon');

            loaderContainer.html(loaderBack).append(loaderIcon);

            loaderContainer.hide();

            if ( this.elem.css('position') != 'absolute' ) {
                this.elem.css({
                    'position' : 'relative'
                });
            }

            this.elem.data('loaderElement', loaderContainer);
            this.elem.data('loader', this);
            this.dom = {
                'container' : loaderContainer,
                'back'      : loaderBack,
                'icon'      : loaderIcon
            };
        }

        nodevoLoader.prototype._position = function() {
            this.dom.icon.css({
                'top': (this.elem[0].offsetHeight / 2 ) - (loaderIcon.height() / 2)
            });

            if ( this.dom.info != undefined ) {
                this.dom.info.css({
                    'top': (this.elem[0].offsetHeight / 2 ) - (loaderIcon.height() / 2)
                });
            }
        }

        nodevoLoader.prototype.start = function() {
            if ( this.elem.data('nLoading') !== true ) {
                this.opts.beforeStart();
                this.elem.data('nLoading', true);

                this.elem.append( this.dom.container );
                this._position();
                this.dom.container.fadeIn(this.opts.speed);
            }
            return this;
        }

        nodevoLoader.prototype.finished = function() {
            var that = this;
            this.dom.container.fadeOut(this.opts.speed, function(){
                $(this).remove();
                that.elem.removeData('nLoading');
                that.opts.afterStop();
            });
            elem.removeData('loader');
        }

        nodevoLoader.prototype.showInfo = function( txt ) {
            var that = this;
            this.dom.icon.fadeOut(function(){
                that.dom.info = $('<div />').addClass('loader-info').html(txt);
                that.dom.container.append(that.dom.info);
                that._position();
                setTimeout(function(){ that.hideInfo() }, 2000);
            });
        }

        nodevoLoader.prototype.hideInfo = function() {
            this.finished();
        }
    }
}