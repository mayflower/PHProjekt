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
 * @version    $Id:$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Timecard.Booking");
dojo.provide("phpr.Timecard.ContentBar");

dojo.declare("phpr.Timecard.Booking", dojo.dnd.Target, {
    onDndDrop:function(source, nodes, copy) {
        if (source.node.id == 'projectBookingSource') {
            dojo.byId('projectId_0').value       = nodes[0].id;
            dojo.byId('projectName_0').innerHTML = nodes[0].innerHTML;
            for (var i in timecardProjectPositions) {
                var id   = timecardProjectPositions[i].id;
                var node = dojo.byId('projectBookingForm_' + id);
                if (node) {
                    dojo.style(node, "display", "none");
                }
            }
            dojo.fadeIn({
                node: dojo.byId('projectBookingForm_0'),
                duration:    1000,
                beforeBegin: function() {
                    var node = dojo.byId('projectBookingForm_0');
                    dojo.style(node, "opacity", 0);
                    dojo.style(node, "display", "block");
                }
            }).play();
            this.onDndCancel(); // cleanup the drop state
        }
    },

    markupFactory:function(params, node) {
        params._skipStartup = true;
        return new phpr.Timecard.Booking(node, params);
    },

    // mouse events
    onMouseDown:function(e) {
        var position = Math.abs(e.target.style.top.replace(/px/, "")) + e.layerY;
        var start = 1;
        for (var i in timecardProjectPositions) {
            var id   = timecardProjectPositions[i].id;
            var node = dojo.byId('projectBookingForm_' + id);
            if (node) {
                dojo.style(node, "display", "none");
            }
        }
        var node = dojo.byId('projectBookingForm_0');
        if (node) {
            dojo.style(node, "display", "none");
        }
        for (var i in timecardProjectPositions) {
            if (start && position >= timecardProjectPositions[i].start && position <= timecardProjectPositions[i].end ) {
                id    = timecardProjectPositions[i].id;
                start = 0;
                dojo.fadeIn({
                    node: dojo.byId('projectBookingForm_' + id),
                    duration: 1000,
                    beforeBegin:function() {
                        var node = dojo.byId('projectBookingForm_' + id);
                        if (node) {
                            dojo.style(node, "opacity", 0);
                            dojo.style(node, "display", "block");
                        }
                    }
                }).play();
            }
        }
        dojo.stopEvent(e);
    }
});

dojo.declare("phpr.Timecard.ContentBar", null, {
    dojoNode: null,
    node:     null,
    start:    8,
    end:      20,

    constructor:function(id) {
        this.dojoNode = dojo.byId(id);
        this.node     = dijit.byId(id);
    },

    getHeight:function() {
        return Math.abs(this.dojoNode.style.height.replace(/px/, ""));
    },

    convertHourToPixels:function(hourHeight, time) {
        var hours   = ((time.substr(0,2) - this.start) * hourHeight);
        var minutes = Math.floor((((time.substr(3,2) / 60)) * hourHeight));
        return hours + minutes;
    },

    convertAmountToPixels:function(hourHeight, time) {
        var hours   = (time.substr(0,2) * hourHeight);
        var minutes = Math.floor((time.substr(3,2)/60) * hourHeight);
        return hours + minutes;
    }
});

dojo.declare("phpr.Timecard.Favorites", dojo.dnd.Source, {
    onDrop:function(source, nodes, copy) {
        if (this != source) {
            this.onDropExternal(source, nodes, copy);
            if (source.node.id == 'projectFavoritesSource') {
                // Add a item
                var id = nodes[0].id.replace(/favoritesTarget-/, "").replace(/favoritesSoruce-/, "");
                dojo.byId('selectedProjectFavorites').value += id + ",";
                dojo.byId('projectBookingSource').innerHTML += '<div class="dojoDndItem dndSource" style="cursor: move;" id="' + id + '">' + nodes[0].innerHTML + '</div><br />';
                projectBookingSource.sync();
            } else if (source.node.id == 'projectFavoritesTarget') {
                // Delete a items
                var tmp = '';
                dojo.byId('projectBookingSource').innerHTML = '';
                projectFavoritesTarget.getAllNodes().forEach(function(node){
                    var id = node.id.replace(/favoritesTarget-/, "").replace(/favoritesSoruce-/, "");
                    var name = node.innerHTML;
                    tmp += id + ',';
                    dojo.byId('projectBookingSource').innerHTML += '<div class="dojoDndItem dndSource" style="cursor: move;" id="' + id + '">' + name + '</div><br />';
                });
                dojo.byId('selectedProjectFavorites').value = tmp;
                projectBookingSource.sync();
            }
        } else {
            this.onDropInternal(nodes, copy);
        }
    },

    markupFactory:function(params, node) {
        params._skipStartup = true;
        return new phpr.Timecard.Favorites(node, params);
    }
});
