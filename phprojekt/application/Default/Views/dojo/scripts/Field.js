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

dojo.provide("phpr.Default.Field");

dojo.require("dijit._editor.plugins.LinkDialog");
dojo.require("dijit._editor.plugins.TextColor");
dojo.require("dijit._editor.plugins.FontChoice");
dojo.require("dojo.date.locale");
dojo.require("dojo.i18n");

dojo.declare("phpr.Default.Field", phpr.Default.System.Component, {
    // summary:
    //    class for rendering form fields
    // description:
    //    this class renders the different form types which are available in a PHProjekt Detail View

    checkRender: function(itemlabel, itemid, itemvalue, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        var itemchecked = false,
            useDisableField = true;
        if (itemvalue == 1) {
            itemchecked = true;
        }

        if (itemdisabled && (itemvalue == 1)) {
            useDisableField = true;
        } else {
            useDisableField = false;
        }

        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.check.html",
            templateData: {
                label:    itemlabel,
                labelfor: (useDisableField) ? itemid + "_disabled" : itemid,
                id:       (useDisableField) ? itemid + "_disabled" : itemid,
                checked:  (itemchecked) ? "checked" : '',
                disabled: (itemdisabled) ? "disabled" : '',
                tooltip:  this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    textFieldRender: function(itemlabel, itemid, itemvalue, itemlength, itemrequired, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.text.html",
            templateData: {
                label:     itemlabel,
                labelfor:  itemdisabled ? itemid + "_disabled" : itemid,
                id:        itemdisabled ? itemid + "_disabled" : itemid,
                value:     itemvalue,
                required:  itemrequired,
                type:      'text',
                disabled:  itemdisabled ? "disabled" : '',
                maxlength: (itemlength > 0) ? 'maxlength="' + itemlength + '"' : '',
                tooltip:   this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    hiddenFieldRender: function(itemlabel, itemid, itemvalue, itemrequired, itemdisabled) {
        phpr.destroyWidget(itemid);
        return new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.hidden.html",
            templateData: {
                label:    itemlabel,
                labelfor: itemid,
                id:       itemid,
                value:    itemvalue,
                required: itemrequired,
                type:     'hidden',
                disabled: (itemdisabled) ? "disabled" : ''
            }
        });
    },

    passwordFieldRender: function(itemlabel, itemid, itemvalue, itemlength, itemrequired, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.text.html",
            templateData: {
                label:     itemlabel,
                labelfor:  (itemdisabled) ? itemid + "_disabled" : itemid,
                id:        (itemdisabled) ? itemid + "_disabled" : itemid,
                value:     itemvalue,
                required:  itemrequired,
                type:      'password',
                maxlength: (itemlength > 0) ? 'maxlength="' + itemlength + '"' : '',
                disabled:  (itemdisabled) ? "disabled" : '',
                tooltip:   this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    uploadFieldRender: function(itemlabel, itemid, itemvalue, itemrequired, itemdisabled, iFramePath, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");

        var fieldId = Math.floor(Math.random() * 100000);

        if (!phpr._uploadCallbacks) {
            phpr._uploadCallbacks = {};
        }

        var deletedHashes = {};
        var lastFileList = [];

        var getFilelistWithoutDeleted = function(filelist) {
            var newlist = [];
            var l = filelist.length;

            for (var i = 0; i < l; i++) {
                if (deletedHashes[filelist[i].hash] !== true) {
                    newlist.push(filelist[i]);
                }
            }

            return newlist;
        };

        var convertFilelistToFormValue = function(filelist) {
            filelist = getFilelistWithoutDeleted(filelist);
            var l = filelist.length;
            var values = [];

            for (var i = 0; i < l; i++) {
                values.push(filelist[i].hash + "|" + filelist[i].fileName);
            }

            return values.join("||");
        };

        var onCheckboxChange = function(hash, value) {
            deletedHashes[hash] = !value;
            refreshList();
        };

        var prettyPrintFileSize = function(size) {
            if (size < 1024) {
                return "" + size + ' bytes';
            } else if (size < 1048576) {
                return "" + Math.floor(size / (1024)) + ' KB';
            } else if (size < 1073741824) {
                return "" + Math.floor(size / (1048576)) + ' MB';
            } else if (size < 1099511627776) {
                return "" + Math.floor(size / (1073741824)) + ' GB';
            }
        };

        var refreshList = dojo.hitch(this, function() {
            this.garbageCollector.collect("uploadListItems");
            widget.uploadList.innerHTML = "";
            var filelist = lastFileList;
            var file, size, ctime, listItem;

            for (var i in filelist) {
                file = filelist[i];
                size = prettyPrintFileSize(file.size);
                ctime = dojo.date.locale.format(new Date(file.ctime * 1000));
                listItem = new phpr.Default.System.TemplateWrapper({
                    templateName: "phpr.Default.template.form.uploadListItem.html",
                    templateData: {
                        downloadLink: file.downloadLink,
                        downloadable: file.downloadLink !== undefined,
                        fileName: file.fileName,
                        size: size,
                        ctime: ctime,
                        checked: deletedHashes[file.hash] === true ? "false" : "true",
                        disabled:  (itemdisabled) ? "true" : "false"
                    }
                });

                listItem.checkbox.onChange = dojo.hitch(this, onCheckboxChange, filelist[i].hash);
                this.garbageCollector.addNode(listItem, "uploadListItems");
                dojo.place(listItem.domNode, widget.uploadList);
            }

            widget.inputField.set('value', convertFilelistToFormValue(filelist));
        });

        var displayError = function(error) {
            dojo.style(widget.errorBox, "display", "block");
            widget.errorBox.innerHTML = error;
        };

        var hideError = function() {
            dojo.style(widget.errorBox, "display", "none");
        };

        phpr._uploadCallbacks[fieldId] = function(error, filelist) {
            if (error) {
                displayError(error);
            } else {
                hideError();
                lastFileList = filelist;
                refreshList();
            }
        };

        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.upload.html",
            templateData: {
                label:      itemlabel,
                labelfor:   (itemdisabled) ? itemid + "_disabled" : itemid,
                id:         (itemdisabled) ? itemid + "_disabled" : itemid,
                value:      itemvalue,
                required:   itemrequired,
                disabled:   (itemdisabled) ? "disabled" : '',
                iFramePath: iFramePath + "/fieldId/" + fieldId,
                tooltip:    this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    percentageFieldRender: function(itemlabel, itemid, itemvalue, itemrequired, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");
        if (!itemvalue || isNaN(itemvalue)) {
            itemvalue = 0;
        }
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.percentage.html",
            templateData: {
                label:    itemlabel,
                labelfor: (itemdisabled) ? itemid + "_disabled" : itemid,
                id:       (itemdisabled) ? itemid + "_disabled" : itemid,
                value:    itemvalue,
                required: itemrequired,
                disabled: (itemdisabled) ? "disabled" : '',
                tooltip:  this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    textAreaRender: function(itemlabel, itemid, itemvalue, itemrequired, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.textarea.html",
            templateData: {
                label:      itemlabel,
                labelfor:   (itemdisabled) ? itemid + "_disabled" : itemid,
                id:         (itemdisabled) ? itemid + "_disabled" : itemid,
                value:      (itemvalue) ?  itemvalue : '\n\n',
                required:   itemrequired,
                disabled:   (itemdisabled) ? "disabled" : '',
                moduleName: phpr.module,
                tooltip:    this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    htmlAreaRender: function(itemlabel, itemid, itemvalue, itemrequired, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");
        var eregHtml = /([<])([^>]{1,})*([>])/i;
        var isHtml   = itemvalue.match(eregHtml);
        var labelAndId = (itemdisabled) ? itemid + "_disabled" : itemid;
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.htmlTextarea.html",
            templateData: {
                label:       itemlabel,
                labelfor:    labelAndId,
                id:          labelAndId,
                value:       (itemvalue) ?  itemvalue : '\n\n',
                required:    itemrequired,
                disabled:    (itemdisabled) ? "disabled" : '',
                moduleName:  phpr.module,
                isHtml:      (isHtml) ? true : false,
                displayHtml: (isHtml) ? 'inline' : 'none',
                displayText: (isHtml) ? 'none' : 'inline',
                textModeTxt: phpr.nls.get('To Text Mode'),
                htmlModeTxt: phpr.nls.get('To HTML Mode'),
                editHtmlTxt: phpr.nls.get('Edit'),
                saveTxt:     phpr.nls.get('Save'),
                tooltip:     this.getTooltip(itemhint)
            }
        });

        this.garbageCollector.addNode('dialogFor_' + labelAndId);
        this.garbageCollector.addNode('editorFor_' + labelAndId);

        return widget;
    },

    dateRender: function(itemlabel, itemid, itemvalue, itemrequired, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.date.html",
            templateData: {
                label:    itemlabel,
                labelfor: (itemdisabled) ? itemid + "_disabled" : itemid,
                id:       (itemdisabled) ? itemid + "_disabled" : itemid,
                value:    itemvalue || phpr.date.getIsoDate(new Date()),
                required: itemrequired,
                disabled: (itemdisabled) ? "disabled" : '',
                tooltip:  this.getTooltip(itemhint),
                invalidDateMessage: this.getInvalidDateMessage()
            }
        });

        return widget;
    },

    timeRender: function(itemlabel, itemid, itemvalue, itemrequired, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.time.html",
            templateData: {
                label:    itemlabel,
                labelfor: (itemdisabled) ? itemid + "_disabled" : itemid,
                id:       (itemdisabled) ? itemid + "_disabled" : itemid,
                value:    itemvalue || phpr.date.getIsoTime(new Date()),
                required: itemrequired,
                disabled: (itemdisabled) ? "disabled" : '',
                tooltip:  this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    datetimeRender: function(itemlabel, itemid, itemvalue, itemrequired, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");
        var date         = (itemvalue) ? phpr.date.isoDatetimeTojsDate(itemvalue) : new Date();
        var valueForDate = phpr.date.getIsoDate(date) || '';
        var valueForTime = phpr.date.getIsoTime(date) || '';
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.datetime.html",
            templateData: {
                label:        itemlabel,
                labelfor:     (itemdisabled) ? itemid + "_disabled" : itemid,
                id:           (itemdisabled) ? itemid + "_disabled" : itemid,
                idForDate:    (itemdisabled) ? itemid + "_disabled_forDate" : itemid + '_forDate',
                idForTime:    (itemdisabled) ? itemid + "_disabled_forTime" : itemid + '_forTime',
                value:        itemvalue || valueForDate + ' ' + valueForTime,
                valueForDate: valueForDate,
                valueForTime: valueForTime,
                required:     itemrequired,
                disabled:     (itemdisabled) ? "disabled" : '',
                tooltip:      this.getTooltip(itemhint),
                invalidDateMessage: this.getInvalidDateMessage()
            }
        });

        return widget;
    },

    selectRender: function(range, itemlabel, itemid, itemvalue, itemrequired, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");
        var options = [];
        var j       = 0;
        var found   = false;
        var first   = null;
        for (j in range) {
            if (null === first) {
                first = range[j].id;
            }
            if (range[j].id == itemvalue) {
                found = true;
            }
            options.push(range[j]);
        }
        if (!found && (null !== first)) {
            itemvalue = first;
        }
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.filterSelect.html",
            templateData: {
                label:    itemlabel,
                labelfor: (itemdisabled) ? itemid + "_disabled" : itemid,
                id:       (itemdisabled) ? itemid + "_disabled" : itemid,
                value:    itemvalue,
                required: itemrequired,
                disabled: (itemdisabled) ? "disabled" : '',
                values:   options,
                tooltip:  this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    multipleSelectRender: function(range, itemlabel, itemid, itemvalue, itemrequired, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");
        var options = [];
        var tmp     = itemvalue.split(',');
        for (var j in range) {
            for (var k in tmp) {
                range[j].selected = '';
                if (tmp[k] == range[j].id) {
                    range[j].selected = 'selected="selected"';
                    break;
                }
            }
            options.push(range[j]);
        }
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.multipleSelect.html",
            templateData: {
                label:    itemlabel,
                labelfor: (itemdisabled) ? itemid + "_disabled" : itemid,
                id:       (itemdisabled) ? itemid + "_disabled" : itemid,
                values:   itemvalue,
                required: itemrequired,
                disabled: (itemdisabled) ? "disabled" : '',
                options:  options,
                tooltip:  this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    multipleFilteringSelectRender: function(range, itemlabel, itemid, itemvalue, itemrequired, itemdisabled, itemhint) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");

        var options = [];
        var tmp     = itemvalue;

        for (var j in range) {
            range[j].selected = '';
            for (var k in tmp) {
                if (parseInt(tmp[k], 10) === range[j].id) {
                    range[j].selected = 'selected="selected"';
                    break;
                }
            }
            options.push(range[j]);
        }

        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.multipleFilteringSelect.html",
            templateData: {
                label:    itemlabel,
                labelfor: (itemdisabled) ? itemid + "_disabled" : itemid,
                id:       (itemdisabled) ? itemid + "_disabled" : itemid,
                values:   itemvalue,
                required: itemrequired,
                disabled: (itemdisabled) ? "disabled" : '',
                options:  options,
                tooltip:  this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    buttonActionRender: function(itemlabel, itemid, itemtext, icon, action, itemhint) {
        phpr.destroyWidget(itemid);
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.actionButton.html",
            templateData: {
                label:    itemlabel,
                labelfor: itemid,
                id:       itemid,
                text:     itemtext,
                icon:     icon,
                action:   action,
                tooltip:  this.getTooltip(itemhint)
            }
        });
        return widget;
    },

    displayFieldRender: function(itemlabel, itemid, itemvalue, itemhint, range) {
        if (null !== range.id) {
            // The Id must be translated into a descriptive String
            for (var j in range) {
                if (range[j]) {
                    if (parseInt(range[j].id, 10) == itemvalue) {
                        itemvalue = range[j].name;
                        break;
                    }
                }
            }
        }
        phpr.destroyWidget(itemid + "_disabled");
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.display.html",
            templateData: {
                label:   itemlabel,
                value:   itemvalue,
                tooltip: this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    ratingFieldRender: function(itemlabel, itemid, itemvalue, itemdisabled, itemhint, itemrange) {
        phpr.destroyWidget(itemid);
        phpr.destroyWidget(itemid + "_disabled");
        var widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.rating.html",
            templateData: {
                label:     itemlabel,
                labelfor:  (itemdisabled) ? itemid + "_disabled" : itemid,
                id:        (itemdisabled) ? itemid + "_disabled" : itemid,
                value:     itemvalue,
                numStars:  itemrange.id,
                disabled:  (itemdisabled) ? "disabled" : '',
                tooltip:   this.getTooltip(itemhint)
            }
        });

        return widget;
    },

    disabledField: function(itemlabel, itemid, itemvalue, itemrequired, itemdisabled) {
        if (itemdisabled) {
            return this.hiddenFieldRender(itemlabel, itemid, itemvalue, itemrequired, false);
        } else {
            return '';
        }
    },

    getTooltip: function(itemhint) {
        return this.render(["phpr.Default.template.form", "tooltip.html"], null, {
            hint: itemhint
        });
    },

    getInvalidDateMessage: function() {
        var bundle = dojo.date.locale._getGregorianBundle(dojo.i18n.normalizeLocale());
        return phpr.nls.get('Invalid date format. Use:') + ' ' + bundle['dateFormat-short'];
    }
});
