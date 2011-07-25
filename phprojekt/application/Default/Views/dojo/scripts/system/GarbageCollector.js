/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Reno Reckling <exi@wthack.de>
 */

dojo.provide("phpr.Default.System.GarbageCollector");
dojo.provide("phpr.Default.System.GarbageCollected");

dojo.declare("phpr.Default.System.GarbageCollector", null, {
    // Dom nodes
    _domNodes: {},
    // Event handler
    _eventHandler: {},

    constructor: function() {
        this._domNodes = {};
        this._eventHandler = {};
    },

    destroy: function() {
        for (var i in this._eventHandler) {
            for (var e in this._eventHandler[i]) {
                this._eventHandler[i][e] = null;
            }
            this._eventHandler[i] = null;
        }

        for (var i in this._domNodes) {
            for (var e in this._domNodes[i]) {
                this._domNodes[i][e] = null;
            }
            this._domNodes[i] = null;
        }
    },

    addNode: function(node, context) {
        // Summary:
        //      Adds a domNode to the garbage collection watch
        // Description:
        //      This adds a domNode or dijit widget to the GC
        //      To provide more flexibility, you can optionally provide a
        //      context.
        if (!this._domNodes[context]) {
            this._domNodes[context] = [];
        }
        this._domNodes[context].push(node);
    },
    addEvent: function(handler, context) {
        // Summary:
        //      Adds a event to the garbage collection watch
        // Description:
        //      This adds a event to the GC
        //      To provide more flexibility, you can optionally provide a
        //      context.
        if (!this._eventHandler[context]) {
            this._eventHandler[context] = [];
        }
        this._eventHandler[context].push(handler);
    },
    collect: function(context) {
        // Summary:
        //      Collect all registered events and nodes from the given scope
        // Description:
        //      This function disconnect all registered event and destroys alls
        //      registered dijit widgets and dom node recursively within the
        //      given context.

        if (this._eventHandler[context] && dojo.isArray(this._eventHandler[context])) {
            while (this._eventHandler[context].length > 0) {
                if (this._eventHandler[context][0]) {
                    dojo.disconnect(this._eventHandler[context][0]);
                }
                this._eventHandler[context][0] = null;
                this._eventHandler[context].splice(0, 1);
            }
        }

        if (this._domNodes[context] && dojo.isArray(this._domNodes[context])) {
            while (this._domNodes[context].length > 0) {
                var n = this._domNodes[context][0];

                if (dijit.byId(n) && dijit.byId(n).destroyRecursive) { // dijit widget?
                    try {
                        dijit.byId(n).destroyRecursive();
                    } catch (e) { // throws error if already destroyed
                    }
                } else if (dojo.byId(n)) { // dom node id?
                    try {
                        var widget = dijit.byNode(dojo.byId(n));
                        if (widget.destroyRecursive) {
                            widget.destroyRecursive();
                        } else if (widget.destroy) {
                            widget.destroy();
                        }
                    } catch (e) {
                        dojo.forEach(dijit.findWidgets(dojo.byId(n)),
                            function(w) {
                                w.destroyRecursive();
                            });
                        dojo.destroy(dojo.byId(n));
                    }
                }

                this._domNodes[context][0] = null;
                this._domNodes[context].splice(0, 1);
            }
        }
    }
});

dojo.declare("phpr.Default.System.GarbageCollected", null, {
    garbageCollector: null,

    constructor: function() {
        this.garbageCollector = new phpr.Default.System.GarbageCollector();
    },

    destroy: function() {
        this.garbageCollector.collect();
    }
});

