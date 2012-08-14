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
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @author     Reno Reckling <reno.reckling@mayflower.de>
 */

dojo.provide("phpr.Default.loadingOverlay");

dojo.declare("phpr.Default.loadingOverlay", phpr.Default.System.Component, {
    // Summary:
    //    Class to overlay multiple given dom elements with a loading screen
    _domNodes: null,
    _overlays: null,
    _message: "Loading",

    constructor: function(domNodes, message) {
        if (!dojo.isArray(domNodes)) {
            domNodes = [domNodes];
        }

        this._domNodes = [];
        this._overlays = [];
        this._message = message !== undefined ? message : this._message;

        for (var i in domNodes) {
            var node = domNodes[i];

            if (!dojo.byId(node)) {
                throw new Error("invalid domNode provided");
            }

            this._domNodes.push(dojo.byId(node));
        }
    },
    show: function() {
        for (var i in this._domNodes) {
            var node = this._domNodes[i];
            var domBox = dojo.marginBox(node);
            var template =  phpr.fillTemplate("phpr.Default.template.loadingOverlay.html",
                {
                    message: this._message
                });

            var overlay = dojo.create('div', { innerHTML: template });

            dojo.style(
                overlay,
                {
                    "height": domBox.h + "px",
                    "width": domBox.w + "px",
                    "position": "absolute",
                    "top": domBox.t + "px",
                    "left": domBox.l + "px",
                    "zIndex": 99999
                }
            );

            dojo.place(overlay, node, "before");
            this._overlays.push(overlay);
        }
    },
    hide: function() {
        var l = this._overlays.length;
        for (var i = 0; i < l; i++) {
            (dojo.hitch(this, function() {
                var overlay = this._overlays[i];
                if (dojo.byId(overlay)) {
                    dojo.fadeOut({
                        node: overlay,
                        duration: 500,
                        onEnd: function() {
                            dojo.destroy(overlay);
                        }
                    }).play();
                }
            }))();
        }
        this._overlays = [];
    }
});

