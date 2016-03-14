/**
 * Classe gérant l'arbre des références.
 * 
 * @author Rémi Leclerc
 */
var Hn_Reference_Tree = function(container, originalSelect) {
    this.container = $(container);
    this.originalSelect = $(originalSelect);

    var that = this;
    this.container.on('ready.jstree', function () {
        that.initEvents();
    });
};


/**
 * @var Element Élément contenant l'arbre
 */
Hn_Reference_Tree.prototype.container;

/**
 * @var Element Le champ de formulaire correspondant à l'arbre
 */
Hn_Reference_Tree.prototype.originalSelect;


/**
 * Initialise les événements.
 */
Hn_Reference_Tree.prototype.initEvents = function () {
    var that = this;
    this.container.on('select_node.jstree', function (e, data) {
        var select = new Nodevo_Form_Select(that.originalSelect);
        select.addValue(data.node.id);
    });
    this.container.on('deselect_node.jstree', function (e, data) {
        var select = new Nodevo_Form_Select(that.originalSelect);
        select.removeValue(data.node.id);
    });
};

/**
 * Affiche l'arbre.
 *
 * @param array options Options
 */
Hn_Reference_Tree.prototype.display = function (options) {
    this.container.jstree(options);
};

/**
 * Sélectionne un élément.
 *
 * @param string|array<string> nodeIds ID(s)
 */
Hn_Reference_Tree.prototype.select = function (nodeIds) {
    if (Array.isArray(nodeIds)) {
        var that = this;
        $(nodeIds).each(function (key, nodeId) {
            that.select(nodeId);
        });
    } else {
        this.container.jstree('select_node', nodeIds);
    }
};

/**
 * désélectionne un élément.
 *
 * @param string nodeId ID
 */
Hn_Reference_Tree.prototype.deselect = function (nodeId) {
    this.container.jstree('deselect_node', nodeId);
};
