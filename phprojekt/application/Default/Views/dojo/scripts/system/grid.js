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

dojo.declare("phpr.grid.cells.Text", dojox.grid.cells._Widget, {
    setValue: function(inRowIndex, inValue) {
        if (this.widget && this.widget.setValue) {
            this.widget.setValue(inValue);
        } else {
            this.inherited(arguments);
        }
    },

    format:function(inRowIndex, inItem) {
        var f, i=this.grid.edit.info, d=this.get ? this.get(inRowIndex, inItem) : (this.value || this.defaultValue);
        if (this.editable && (this.alwaysEditing || (i.rowIndex==inRowIndex && i.cell==this))){
            return this.formatEditing(d, inRowIndex);
        } else {
            var maxLength = 25;

            d = d.toString();

            var output = d.replace(/&/g, "&amp;");
            output     = output.replace(/</g, "&lt;");
            output     = output.replace(/>/g, "&gt;");

            // Only if there were not converted html entities, strip string if it exceeds max length
            // That is because if the string is cut by inside an html entity, it will be shown wrong:
            if (d == output) {
                if (output.length > maxLength) {
                    output = output.substr(0, maxLength) + '...';
                }
            }
            return output;
        }
    }
});

dojo.declare("phpr.grid.cells.Textarea", dojox.grid.cells._Widget, {
    setValue: function(inRowIndex, inValue) {
        if (this.widget && this.widget.setValue) {
            this.widget.setValue(inValue);
        } else {
            this.inherited(arguments);
        }
    },

    format:function(inRowIndex, inItem) {
        var f, i=this.grid.edit.info, d=this.get ? this.get(inRowIndex, inItem) : (this.value || this.defaultValue);
        if (this.editable && (this.alwaysEditing || (i.rowIndex==inRowIndex && i.cell==this))){
            return this.formatEditing(d, inRowIndex);
        } else {
            output = this.strip_tags(d);
            return output;
        }
    },

    strip_tags:function(str, allowed_tags) {
        // Summary
        //    Strip tags function by Kevin van Zonneveld (http://kevin.vanzonneveld.net) improved by Luke Godfrey
        // Example of use
        //    strip_tags('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
        //    Returns: 'Kevin <b>van</b> <i>Zonneveld</i>'

        var key = '', allowed = false;
        var matches = [];
        var allowed_array = [];
        var allowed_tag = '';
        var i = 0;
        var k = '';
        var html = '';

        var replacer = function(search, replace, str) {
            return str.split(search).join(replace);
        };

        // Build allowes tags associative array
        if (allowed_tags) {
            allowed_array = allowed_tags.match(/([a-zA-Z]+)/gi);
        }

        str += '';

        // Match tags
        matches = str.match(/(<\/?[\S][^>]*>)/gi);

        // Go through all HTML tags
        for (key in matches) {
            if (isNaN(key)) {
                // IE7 Hack
                continue;
            }

            // Save HTML tag
            html = matches[key].toString();

            // Is tag not in allowed list? Remove from str!
            allowed = false;

            // Go through all allowed tags
            for (k in allowed_array) {
                // Init
                allowed_tag = allowed_array[k];
                i = -1;

                if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+'>');}
                if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+' ');}
                if (i != 0) { i = html.toLowerCase().indexOf('</'+allowed_tag)   ;}

                // Determine
                if (i == 0) {
                    allowed = true;
                    break;
                }
            }

            if (!allowed) {
                str = replacer(html, "", str); // Custom replace. No regexing
            }
        }
        return str;
    }
});

var dgc = dojox.grid.cells;
dgc.DateTextBox.markupFactory = function(node, cell){
    dgc._Widget.markupFactory(node, cell);
};
