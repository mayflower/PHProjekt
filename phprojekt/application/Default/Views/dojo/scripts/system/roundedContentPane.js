/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.roundedContentPane");

dojo.declare("phpr.roundedContentPane", [dijit.layout.ContentPane, dijit._Templated], {
    // radius: Integer
    // radius of the corners
    radius: 15,

    // moveable: Boolean
    // if true, the node is movable by either the containerNode, or an optional node
    // found by the handle attribute
    moveable: false,

    // handle: String
    // a CSS3 selector query to match the handle for this node, scoped to this.domNode
    handle: ".handle",

    //color
    color:       [200, 232, 249, 0.5],
    strokeColor: [217, 232, 249, 1],

    // template:
    templateString:
        '<div><div style="position:relative;">' +
            '<div dojoAttachPoint="surfaceNode"></div>' +
            '<div dojoAttachPoint="containerNode" class="roundedPaneInner"></div>' +
        '</div></div>',

    startup:function() {
        this.inherited(arguments);
        this._initSurface();
        dojo.style(this.surfaceNode, {
            position: "absolute",
            top:      0,
            left:     0
        });

        if (this.moveable) {
            this._mover = new dojo.dnd.TimedMoveable(this.domNode, {
                handle: dojo.query(this.handle, this.domNode)[0] ||this.containerNode, timeout: 69
            });
        }
    },

    _initSurface:function() {
        var s      = dojo.marginBox(this.domNode);
        var stroke = 5;

        this.surface = dojox.gfx.createSurface(this.surfaceNode, s.w + stroke * 2, s.h + stroke * 2);
        this.roundedShape = this.surface.createRect({
            r:      this.radius,
            width:  s.w,
            height: s.h
        })
        .setFill(this.color) // black, 50% transparency
        .setStroke({ color:this.strokeColor, width:stroke }) // solid white
        ;

        this.resize(s);
    },

    resize:function(size) {
        if (!this.surface) {
            this._initSurfce();
        }

        this.surface.setDimensions(size.w, size.h);
        this.roundedShape.setShape({
            width:  size.w,
            height: size.h
        });

        var _offset = Math.floor(this.radius / 2);
        dojo.style(this.containerNode, {
            position: "absolute",
            overflow: "auto",
            top:      _offset + "px",
            left:     _offset + "px",
            Height:  (size.h - _offset * 4) + "px",
            width:   (size.w - _offset * 4) + "px"
        });
    }
});
