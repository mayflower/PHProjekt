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
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Default.System.Component");

dojo.require("dojo.NodeList-traverse");
dojo.require("phpr.Default.System.GarbageCollector");

dojo.declare("phpr.Default.System.Component", phpr.Default.System.GarbageCollected, {
    main:   null,
    module: "",
    _destroyed: false,
    destroy: function() {
        this._destroyed = true;
        this.inherited(arguments);
    },
    render: function(template, node, data) {
        content = phpr.fillTemplate(template[0] + "." + template[1], data);

        // [a-zA-Z1-9[]:|]
        var eregId = /id=\\?["'][\w\x5b\x5d\x3a\x7c]*\\?["']/gi;
        var result = content.match(eregId);
        if (result) {
            for (var i = 0; i < result.length; i++) {
                var id = result[i].replace(/id=\\?["']/gi, '').replace(/\\?["']/gi, '');
                if (dijit.byId(id)) {
                    try { // may fail due to already removed dom node
                        dijit.byId(id).destroyRecursive();
                    } catch (e) {
                        try { // if this fails, it's probably long gone
                            dijit.byId(id).destroy();
                        } catch (e) {}
                    }
                }
            }
        }

        if (node) {
            var dojoType = node.getAttribute('dojoType');
            phpr.destroySubWidgets(node);
            if ((dojoType == 'dijit.layout.ContentPane') ||
                    (dojoType == 'dijit.layout.BorderContainer')) {
                dijit.byNode(node).set('content', content);
                dijit.byId(node.getAttribute('id')).resize();
            } else {
                if (dijit.byId(node) && dijit.byId(node).set) {
                    dijit.byId(node).set('content', content);
                } else {
                    node.innerHTML = content;
                    phpr.initWidgets(node);
                }
            }
        } else {
            return content;
        }
    },

    publish: function(/*String*/ name, /*Array*/ args) {
        // summary:
        //    Publish the topic for the current module, its always prefixed with the module.
        // description:
        //    I.e. if the module name is "project" this.publish("open)
        //    will then publish the topic "project.open".
        // name: String
        //    The topic of this module that shall be published.
        // args: Array
        //    Arguments that should be published with the topic
        dojo.publish(phpr.module + "." + name, args);
    },

    subscribe: function(/*String*/ name, /*String or null*/ context, /*String or function*/ method) {
        // summary:
        //    Subcribe topic which was published for the current module, its always prefixed with the module.
        // description:
        //    I.e. if the module name is "project" this.subscribe("open)
        //    will then subscribe the topic "project.open".
        // name: String
        //    The topic of this module that shall be published.
        // args: Array
        //    Arguments that should be published with the topic
        dojo.subscribe(phpr.module + "." + name, context, method);
    }
});
