/**
 * Manages hobbies collection field
 *
 * The ul tag that includes all fields in the collection must be in the twig view (ul#reference_hobbies_form)
 * as well as the add button (add-hobby)
 *
 * @TODO: This class should be more genric to allow management of all collection type fields
 */
var HobbyCollection = (function() {

    HobbyCollection = function() {
        this.ulForm = null;
        this.addLinkSelector = $('.add-hobby');

        this.init();
    };

    HobbyCollection.prototype = {
        init: function() {
            var self = this;
            this.ulForm = $('ul#reference_hobbies_form');

            this.ulForm.find('li').each(function() {
                self.addDeleteLink($(this));
            });
            this.addLinkSelector.on('click', function() {
                self.addForm();
            });
        },

        addDeleteLink: function (element) {
            var deleteLink = $('<em class="fa fa-trash-o" style="color: #d60030;"></em>');
            var deleteLinkLayout = $('<a title="Retirer le centre d\'intérêt de la liste"></a>');
            deleteLinkLayout.append(deleteLink);
            element.append(deleteLinkLayout);

            deleteLink.on('click', function() {
                if (confirm('Supprimer ce centre d\'intérêt ?')) {
                    element.hide('slow', function() { $(this).remove(); });
                }
            });
        },

        addForm: function () {
            var prototype = this.ulForm.attr('data-prototype');

            var formNumber = this.ulForm.children().length;
            var newField = prototype.replace(/__name__/g, formNumber);
            var newFieldLayout = $('<li class="col-md-4" style="display:flex; align-items: center;"></li>').append(newField);

            this.addDeleteLink(newFieldLayout);
            this.ulForm.append(newFieldLayout);

            newFieldLayout.show();
        }
    };

    return HobbyCollection;
})();
