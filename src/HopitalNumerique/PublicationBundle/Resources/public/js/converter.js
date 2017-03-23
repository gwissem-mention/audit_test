var WordConverter;
(function() {
    WordConverter = function (button, viewport, uploadFormUrl, prepareFormUrl) {
        this.button = button;
        this.viewport = viewport;
        this.uploadFormUrl = uploadFormUrl;
        this.prepareFormUrl = prepareFormUrl;

        this.fileFormWrapper = null;
        this.fileForm = null;
        this.prepareForm = null;

        this.icons = {
            file: 'fa-file-word-o',
            waiting: 'fa-spinner fa-spin',
            submit: 'fa-upload'
        };

        this.callbacks = {
            prepareFormLoaded: $.Callbacks()
        };

        this.init();
    };

    WordConverter.storage = {
        'done': "converter_done"
    };

    WordConverter.prototype = {
        init: function () {
            this.bindEvents();
            this.loadPrepareForm();
        },

        bindEvents: function () {
            this.button.on('click', $.proxy(this.handleFileUploadTrigger, this));
            $('.options .nav li').click($.proxy(this.unloadUploadForm, this));
        },

        /**
         * Handle click on upload button. Depends on state, load upload form or handle uploaded file
         * @param event
         */
        handleFileUploadTrigger: function (event) {
            event.preventDefault();
            this.setButtonState('waiting');

            if (null !== this.fileForm) {
                this.uploadDocumentFile($.proxy(this.setIdleState, this));
            } else {
                this.loadUploadForm(
                    $.proxy(function () {
                        this.setButtonState('submit');
                    }, this)
                );
            }
        },

        /**
         * Load upload form
         * @param callback
         */
        loadUploadForm: function (callback) {
            var instance = this;
            $.get(this.uploadFormUrl, function (html) {
                var $wrapper = $('<div />')
                    .html(html)
                    .appendTo('body')
                    .css({
                        position: 'absolute',
                        top: instance.button.offset().top,
                        left: instance.button.offset().left + instance.button.outerWidth()
                    })
                ;

                instance.fileFormWrapper = $wrapper;
                instance.fileForm = $wrapper.find('form').get(0);

                $(instance.fileForm).on('submit', function (e) {
                    e.preventDefault();
                    instance.setButtonState('waiting');
                    instance.uploadDocumentFile($.proxy(instance.setUploadState, instance));
                });

                if (typeof callback === 'function') {
                    callback();
                }
            });
        },

        /**
         * Load Prepare Form.
         */
        loadPrepareForm: function() {
            var instance = this;
            this.setLoadingState();

            var query = $.get(this.prepareFormUrl, function (response) {
                if (response !== undefined) {
                    instance.viewport.html(response);
                    instance.prepareForm = $('form', response).get(0);
                    instance.handlePrepareForm();
                }
            });

            query.complete(function() {
                instance.setIdleState();
            });
        },

        /**
         * Handle prepare form actions and submit
         *
         * The Prepare form is loaded inside another form element. We must create a FormData object
         * manually to submit it via ajax.
         */
        handlePrepareForm: function() {
            var instance = this;

            var formElements = [];
            for (var i = 0; i < this.prepareForm.elements.length; i++) {
                formElements.push(
                    document.getElementById(this.prepareForm.elements.item(i).id)
                );
            }

            $('.node-actions', this.viewport).each(function () {
                var $elem = $(this);

                $('.exclude input', $elem).change(function() {
                   if ($(this).prop('checked')) {
                       $elem.find('.squash input')
                           .prop('checked', false)
                           .prop('disabled', true)
                       ;
                   } else {
                       $elem.find('.squash input')
                           .prop('disabled', false)
                       ;
                   }
                });
            });

            // @TODO : dynamiser le nom du bouton ?
            $('#submit-prepare-form').on('click', function (e) {
                e.preventDefault();

                var formData = new FormData();

                for (var i in formElements) {
                    if (formElements[i].type === "checkbox") {
                        if (formElements[i].checked) {
                            formData.append(formElements[i].name, formElements[i].checked);
                        }
                    } else {
                        formData.append(formElements[i].name, formElements[i].value);
                    }
                }

                var request = new XMLHttpRequest();
                request.open(instance.prepareForm.method, instance.prepareForm.action);
                request.send(formData);

                request.onload = function(event) {
                    if (request.status === 200) {
                        sessionStorage.setItem(WordConverter.storage.done, "true");
                        window.location.reload();
                    } else {
                        instance.viewport.html("Error " + request.status + " occurred uploading your file.<br \/>");
                    }
                };
            });

            instance.callbacks.prepareFormLoaded.fire();
        },

        unloadUploadForm: function () {
            if (null !== this.fileFormWrapper) {
                this.fileFormWrapper.remove();
                this.fileFormWrapper = null;
                this.fileForm = null;
                this.setButtonState('file');
            }
        },

        /**
         * Submit file upload form
         * @param callback
         * @returns {boolean}
         */
        uploadDocumentFile: function (callback) {

            if (null === this.fileForm) {
                return false;
            }

            var instance = this;
            var formData = new FormData(this.fileForm);

            $.ajax({
                url: this.fileForm.action,
                method: this.fileForm.method,
                data: formData,
                processData: false,
                contentType: false
            })
                .done(function (response) {
                    instance.viewport.html(response);
                    instance.prepareForm = $('form', response).get(0);
                    instance.handlePrepareForm();
                    instance.unloadUploadForm();
                })
                .fail(function (request) {
                    if (request.status === 400) {
                        instance.viewport.html('');
                        var response = request.responseJSON;
                        instance.viewport.append(response.messages.join("\n")).css({"color": "red"});
                    }
                })
                .complete(function (request) {
                    if (typeof callback === 'function') {
                        callback();
                    }
                })
            ;
        },

        setButtonState: function (state) {
            var $i = this.button.find('i');
            for (var i in this.icons) {
                $i.removeClass(this.icons[i]);
            }

            $i.addClass(this.icons[state]);
        },

        setIdleState: function () {
            this.setButtonState('file');
        },

        setLoadingState: function () {
            this.setButtonState('waiting');
        },

        setUploadState: function () {
            this.setButtonState('submit');
        },

        onPrepareFormLoaded: function (callback) {
            this.callbacks.prepareFormLoaded.add(callback);
        }
    };

    $(document).ready(function () {
        if (sessionStorage.getItem(WordConverter.storage.done)) {
            var $trigger = $('a.btn-report');
            if ($trigger.length > 0) {
                $trigger.trigger('click');
                sessionStorage.removeItem(WordConverter.storage.done);
            }
        }
    });
})();
