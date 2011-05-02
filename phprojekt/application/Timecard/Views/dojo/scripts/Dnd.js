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
 * @subpackage Timecard
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.ContentBar");
dojo.provide("phpr.Timecard.Favorites");

dojo.declare("phpr.Timecard.ContentBar", null, {
    // Summary:
    //    Helper class for calculate the heights of the booked times.
    _dojoNode: null,

    constructor:function(id) {
        // Summary:
        //    Init the helper with the dojo node.
        this._dojoNode = dojo.byId(id);
    },

    getHeight:function() {
        // Summary:
        //    Get the height of the dojo node.
        return Math.abs(this._dojoNode.style.height.replace(/px/, ''));
    },

    convertHourToPixels:function(hourHeight, time) {
        // Summary:
        //    Convert a time into height.
        var hours   = (time.substr(0, 2) * hourHeight);
        var minutes = Math.floor((((time.substr(3, 2) / 60)) * hourHeight));

        return hours + minutes;
    }
});

dojo.declare("phpr.Timecard.Favorites", dojo.dnd.Source, {
    // Summary:
    //    Extend the dojo.dnd.Source for set the height onDrop.
    onDrop:function(source, nodes, copy) {
        // Summary:
        //    Extend the onDrop for set a fixed height.
        if (this != source) {
            this.onDropExternal(source, nodes, copy);
            if (source.node.id == 'projectFavoritesSource-Timecard') {
                dojo.style('projectFavoritesTarget-Timecard', 'height', '250px');
            } else if (source.node.id == 'projectFavoritesTarget-Timecard') {
                dojo.style('projectFavoritesSource-Timecard', 'height', '250px');
            }
        } else {
            this.onDropInternal(nodes, copy);
        }
    },

    markupFactory:function(params, node) {
        // Summary:
        //    Needed by dojo.
        params._skipStartup = true;
        return new phpr.Timecard.Favorites(node, params);
    }
});

dojo.dnd._defaultCreator = function(node) {
    // Summary:
    //    Overwrite the dojo function for set a div with fixed it.
	return function(item, hint) {
		var isObj = item && dojo.isObject(item), data, type, n;
		data  = (isObj && item.data) ? item.data : item;
		type  = (isObj && item.type) ? item.type : ['text'];
		n     = dojo.create('div', {innerHTML: data});
		n.id  = item.id

		return {node: n, data: data, type: type};
	};
};
