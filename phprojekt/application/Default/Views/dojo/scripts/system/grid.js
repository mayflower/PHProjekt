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

dojo.provide("phpr.grid");
dojo.provide("phpr.grid.cells.Select");

phpr.grid.formatDateTime = function(date) {
    if (!date || !String(date).match(/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/)) {
        return date;
    }
    var iso = String(date).replace(" ", "T"); // Make it a real date ISO string
    var dateObj = dojo.date.stamp.fromISOString(iso);
    return dojo.date.locale.format(dateObj, {formatLength:'short', selector:'dateTime'});
};

phpr.grid.formatTime = function(value) {
    var isoRegExp = /^(?:(\d{2})(\d{2})?)$/;
    var match = isoRegExp.exec(value);
    if (match) {
        match.shift();
        return match[0] + ':' + match[1];
    } else {
        return value;
    }
},

phpr.grid.formatText = function(value) {
    // Summary:
    //    Converts characters '<', '>' and '&' into readable HTML entities. Example: receives 'This is very
    // <important>' and returns 'This is very &lt;important&gt;'
    //    If there weren't converted any characters and length of string exceeds maximum allowed length, it is stripped

    var maxLength = 25;

    value = value.toString();

    var output = value.replace(/&/g, "&amp;");
    output     = output.replace(/</g, "&lt;");
    output     = output.replace(/>/g, "&gt;");

    if (value == output) {
        if (output.length > maxLength) {
            output = output.substr(0, maxLength) + '...';
        }
    }

    return output;
},

phpr.grid.formatUpload = function(value) {
    if (value.indexOf('|') > 0) {
        files = value.split('||');
        value = '';
        for (p in files) {
            if (p > 0) {
                value += ', ';
            }
            value += files[p].substring(files[p].indexOf('|') + 1, files[p].length);
        }
    }
    return value;
},

dojo.declare("phpr.grid.cells.Percentage", dojox.grid.cells._Widget, {
    // summary:
    //    Redefine the function to return the correct value
    // description:
    //    Redefine the function to return the correct value
    widgetClass: "dijit.form.HorizontalSlider",

    getValue:function(inRowIndex) {
        return dojo.number.round(this.widget.attr('value'), 1);
    },

    format:function(inRowIndex, inItem) {
        var f, i=this.grid.edit.info, d=this.get ? this.get(inRowIndex, inItem) : (this.value || this.defaultValue);
        if (this.editable && (this.alwaysEditing || (i.rowIndex==inRowIndex && i.cell==this))){
            return this.formatEditing(d, inRowIndex);
        } else {
            var v = dojo.number.round(d, 1);
            return (typeof v == "undefined" ? this.defaultValue : v);
        }
    }
});

dojo.declare("phpr.grid.cells.Select", dojox.grid.cells.Select, {
    // summary:
    //    Redefine the function to return the correct value
    // description:
    //    Redefine the function to return the correct value
    format:function(inRowIndex, inItem) {
        var f, i=this.grid.edit.info, d=this.get ? this.get(inRowIndex, inItem) : (this.value || this.defaultValue);
        if (this.editable && (this.alwaysEditing || (i.rowIndex==inRowIndex && i.cell==this))){
            return this.formatEditing(d, inRowIndex);
        } else {
            var v = '';
            for (var i=0, o; ((o=this.options[i]) !== undefined); i++){
                if (d == this.values[i]) {
                    v = o;
                }
            }
            return (typeof v == "undefined" ? this.defaultValue : v);
        }
    }
});

dojo.declare("phpr.grid.cells.DateTextBox", dojox.grid.cells.DateTextBox, {
    // summary:
    //    Redefine the function to work with iso format
    // description:
    //    Redefine the function to work with iso format
    widgetClass: "dijit.form.DateTextBox",

    getValue:function(inRowIndex) {
        var date = this.widget.attr('value');
        var day = date.getDate();
        if (day < 10) {
            day = '0'+day;
        }
        var month = (date.getMonth()+1);
        if (month < 10) {
            month = '0'+month
        }
        return date.getFullYear() + '-' + month + '-' + day;
    },

    setValue:function(inRowIndex, inValue) {
        if (this.widget) {
            var parts = inValue.split("-");
            var year  = parts[0];
            var month = parts[1]-1;
            var day   = parts[2];
            this.widget.attr('value', new Date(year, month, day));
        } else {
            this.inherited(arguments);
        }
    },

    getWidgetProps:function(inDatum) {
        var parts = inDatum.split("-");
        var year  = parts[0];
        var month = parts[1]-1;
        var day   = parts[2];
        return dojo.mixin(this.inherited(arguments), {
            value: new Date(year, month, day)
        });
    }
});
var dgc = dojox.grid.cells;
dgc.DateTextBox.markupFactory = function(node, cell){
    dgc._Widget.markupFactory(node, cell);
};
