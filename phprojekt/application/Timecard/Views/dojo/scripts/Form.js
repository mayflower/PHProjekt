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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.Form");

dojo.require("dijit.form.Button");
dojo.require("dijit.TooltipDialog");

dojo.declare("phpr.Timecard.Form", phpr.Default.System.Component, {
    dateObject: null,
    formdata: [],
    id: 0,
    sendData: [],
    _bookUrl: null,
    _date: null,
    _favoriteButton: null,
    _favoritesUrl: null,
    _manFavBoxesHeight: 18,
    _templateRenderer: null,
    _timecardTooltipDialog: null,
    _url: null,
    _hourHeight: 40,

    constructor: function(main, date) {
        // Summary:
        //    Render the form on construction
        // Description:
        //    This function receives the form data from the server and renders the corresponding form
        this.main          = main;
        this.id            = 0;
        this._favoritesUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetFavoritesProjects';
        this._url          = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/1/id/' + this.id;

        // Fixed hours 0-24
        var hours    = [];
        var show     = '';
        var rowClass = 'dayViewWhite';
        for (var i = 0; i < 24; i++) {
            for (j = 0; j < 2; j++) {
                show = '';
                if (rowClass == 'dayViewCelestial') {
                    rowClass = 'dayViewWhite';
                } else {
                    rowClass = 'dayViewCelestial';
                }
                if (i < 10) {
                    show = '0';
                }
                if (j === 0) {
                    hour = i + ':00';
                    show = show + i + ':00';
                } else {
                    hour = i + ':30';
                    show = show + i + ':30';
                }
                hours.push({
                    "hour":    hour,
                    "display": show,
                    "class":   rowClass
                });
            }
        }
        this._dayView = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Timecard.template.dayView.html",
            templateData: {
                hours: hours,
                tooltipHelpTxt: phpr.nls.get('Click for open the form')
            }
        });

        this.main._contentWidget.dayView.set('content', this._dayView);

        this.main._contentWidget.dayView.domNode.scrollTop = 320;

        this.setDate(date);
        this.loadView();
    },

    destroy: function() {
        this._destroyTemplateRenderer();
        this.inherited(arguments);
    },

    _destroyTemplateRenderer: function() {
        if (this._templateRenderer && dojo.isFunction(this._templateRenderer.destroy)) {
            this._templateRenderer.destroy();
        }
        this._templateRenderer = null;
    },

    setDate: function(date) {
        // Summary:
        //    Set the date for use in the form
        // Description:
        //    Set the date for use in the form
        if (undefined === date) {
            this.dateObject = new Date();
        } else {
            this.dateObject = date;
        }
        this._date = phpr.date.getIsoDate(this.dateObject);
    },

    loadView: function() {
        // Summary:
        //    Load all the form views
        // Description:
        //    Load all the form views
        this.drawDayView();
        this.setFavoriteButton();
    },

    drawDayView: function() {
        // Summary:
        //    Render the Day View
        // Description:
        //    Render the Day View
        this._bookUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDayList/date/' + this._date;
        phpr.DataStore.addStore({url: this._bookUrl});
        phpr.DataStore.requestData({url: this._bookUrl, processData: dojo.hitch(this, function() {
            var data       = phpr.DataStore.getData({url: this._bookUrl});

            // Clean "Day View"
            this._dayView.projectBookingContainer.destroyDescendants();

            // Draw hours block
            for (var i in data) {
                var dndClass = 'dndTarget';
                // Open period
                var endTime  = data[i].endTime;
                if (null === data[i].endTime) {
                    endTime      = data[i].startTime;
                } else if (data[i].endTime == '00:00' || data[i].endTime == '00:00:00') {
                    endTime = '24:00';
                }

                var start = this._convertHourToPixels(data[i].startTime);
                var end   = this._convertHourToPixels(endTime);
                var top   = start + 'px';
                var height;
                height = (end - start);
                var minPixel = this._convertHourToPixels("00:15");
                height = Math.max(height, minPixel) - 6;
                height += "px";

                var tmp       = dojo.create("div");
                tmp.id        = 'targetBooking' + data[i].id;
                tmp.innerHTML = data[i].display;
                if (data[i].projectId == 1) {
                    tmp.innerHTML = "unassigned";
                }
                dojo.addClass(tmp, dndClass);
                dojo.style(tmp, "top", top);
                dojo.style(tmp, "height", height);
                if (parseInt(height) <= 4) {
                    if (dojo.isIE && dojo.isIE < 8) {
                        lineHeight = 0.5;
                    } else {
                        lineHeight = 0;
                    }
                    dojo.style(tmp, "lineHeight", lineHeight);
                } else if (parseInt(height) < 14) {
                    if (dojo.isIE && dojo.isIE < 8) {
                        lineHeight = 1;
                    } else {
                        lineHeight = 0.5;
                    }
                    dojo.style(tmp, "lineHeight", lineHeight);
                }
                this._dayView.projectBookingContainer.domNode.appendChild(tmp);
                this.garbageCollector.addEvent(
                    dojo.connect(tmp, "onclick",  dojo.hitch(this, "fillForm", data[i].id)));
            }
        })});
    },

    setFavoriteButton: function() {
        // Summary:
        //    Set the favorites button
        // Description:
        //    Set the favorites button
        if (this._destroyed) {
            return;
        }

        if (this._favoriteButton === null) {
            var params = {
                label:     phpr.nls.get('Manage project list'),
                showLabel: true,
                baseClass: "positive",
                disabled:  false
            };
            this._favoriteButton = new dijit.form.Button(params);
            phpr.viewManager.getView().buttonRow.domNode.appendChild(this._favoriteButton.domNode);
            this.garbageCollector.addNode(this._favoriteButton);
            this.garbageCollector.addEvent(
                dojo.connect(this._favoriteButton, "onClick",  dojo.hitch(this, "openManageFavorites")));
        }
    },

    drawFormView: function(node, date, start, end, project, notes) {
        // Summary:
        //    Render the form and the favorites
        // Description:
        //    Render the form and the favorites
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/1/id/' + this.id;
        phpr.DataStore.addStore({url: this._favoritesUrl});
        phpr.DataStore.requestData({url: this._favoritesUrl, processData: dojo.hitch(this, function() {
            phpr.DataStore.addStore({url: this._url});
            phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
                if (!this._timecardTooltipDialog) {
                    var favorites = phpr.DataStore.getData({url: this._favoritesUrl});
                    var meta      = phpr.DataStore.getMetaData({url: this._url});

                    this._templateRenderer = new phpr.Default.Field();

                    // Init formdata
                    var formData = [];
                    // startDatetime
                    formData.push(this._templateRenderer.datetimeRender(meta[0].label, meta[0].key, '',
                        meta[0].required, false, meta[0].hint));
                    // endTime
                    formData.push(this._templateRenderer.timeRender(meta[1].label, meta[1].key, '',
                        meta[1].required, false, meta[1].hint));
                    // projectId
                    var range = dojo.clone(meta[3].range);
                    range.unshift({ 'id': -1, 'name': '----' });
                    for (var i in favorites) {
                        var id = parseInt(favorites[i].id);
                        if (id > 0) {
                            for (var j in range) {
                                if (range[j].id == id) {
                                    delete range[j];
                                    break;
                                }
                            }
                            range.unshift({'id': parseInt(favorites[i].id), 'name': favorites[i].name});
                        }
                    }

                    var l = range.length;
                    for (var i = 0; i < l; i++) {
                        if (range[i].id == 1) {
                            range[i].name = "unassigned";
                            break;
                        }
                    }
                    formData.push(this._templateRenderer.selectRender(range, meta[3].label, meta[3].key, -1,
                        meta[3].required, false, meta[3].hint));
                    // notes
                    formData.push(this._templateRenderer.textAreaRender(meta[4].label, meta[4].key, '',
                        meta[4].required, false, meta[4].hint));

                    // timecardId
                    formData.push(this._templateRenderer.hiddenFieldRender('', 'timecardId', this.id, true, false));

                    this._formContent = new phpr.Default.System.TemplateWrapper({
                        templateName: "phpr.Timecard.template.formView.html",
                        templateData: {
                            saveText: phpr.nls.get('Save'),
                            deleteText: phpr.nls.get('Delete')
                        }
                    });

                    this.garbageCollector.addNode(this._formContent);

                    for (var i in formData) {
                        dojo.place(formData[i].domNode, this._formContent.formBottom, 'before');
                    }

                    this._timecardTooltipDialog = new dijit.TooltipDialog({
                        'class': 'timecardTooltipDialog',
                        content: this._formContent,
                        orient: function() {
                            this.domNode.className = this["class"] + " dijitTooltipABLeft dijitTooltipRight";
                        },
                        onBlur: function() {
                            dijit.popup.close(this);
                        },
                        onCancel: function() {
                            dijit.popup.close(this);
                        }
                    });

                    this.garbageCollector.addNode(this._timecardTooltipDialog);

                    this.garbageCollector.addEvent(
                        dojo.connect(this._formContent.saveBookingButton, "onClick", dojo.hitch(this, "submitForm")));
                    this.garbageCollector.addEvent(
                        dojo.connect(this._formContent.deleteBookingButton, "onClick", dojo.hitch(this, function() {
                            phpr.confirmDialog(dojo.hitch(this, "deleteForm"),
                            phpr.nls.get('Are you sure you want to delete?'));
                        })));
                } else {
                    dijit.byId('timecardId').set('value', this.id);
                }

                dijit.popup.open({
                    parent: node,
                    popup: this._timecardTooltipDialog,
                    around: node,
                    orient: {'TL': 'BL', 'TR': 'BR'}
                });
                dojo.byId('projectId').focus();
                this.updateForm(date, start, end, project, notes);
            })});
        })});
    },

    createFavoritesDialog: function(allProjects, favoritesList) {
        // Summary:
        //    Render the dialog for manage favorites
        // Description:
        //    Render the dialog for manage favorites
        if (!this._favoritesDialogContent) {
            this._favoritesDialogContent = new phpr.Default.System.TemplateWrapper({
                templateName: "phpr.Timecard.template.favoritesDialog.html",
                templateData: {
                    titleTxt: phpr.nls.get('Drag the projects from left to right'),
                    helpTxt: phpr.nls.get('Favorite projects appear first in the select box of the form'),
                    allProjects: allProjects,
                    favoritesList: favoritesList
                }
            });

            this.main._contentWidget.dialogContent.set('content', this._favoritesDialogContent);

            this.garbageCollector.addNode(this._favoritesDialogContent);

            // Event buttons
            this.garbageCollector.addEvent(
                    dojo.connect(this._favoritesDialogContent.favoritesDialogButton, "onClick",
                        dojo.hitch(this.main._contentWidget.manageFavorites, "hide")));

            // Event buttons
            this.garbageCollector.addEvent(
                    dojo.connect(this.main._contentWidget.manageFavorites, "hide",  dojo.hitch(this, "submitFavoritesForm")));
        }
    },

    prepareSubmission: function() {
        // Summary:
        //    Correct some data before send it to the server
        // Description:
        //    Correct some data before send it to the server
        if (this.sendData.endTime) {
            this.sendData.endTime = phpr.date.getIsoTime(this.sendData.endTime);
        }
        if (this.sendData.startTime) {
            this.sendData.startTime = phpr.date.getIsoTime(this.sendData.startTime);
        }
        if (this.sendData.projectId < 0) {
            this.sendData.projectId = 0;
        }
        if (this.sendData.notes == "\n") {
            this.sendData.notes = "";
        }

        if (!this._formContent.bookingForm.isValid()) {
            this._formContent.validate();
            return false;
        }

        return true;
    },

    submitForm: function(event) {
        // Summary:
        //    Save the booking form
        // Description:
        //    Save the booking form and reload the views
        this.id       = dijit.byId('timecardId').get('value');
        this.sendData = [];
        this.sendData = dojo.mixin(this.sendData, this._formContent.bookingForm.get('value'));
        if (!this.prepareSubmission()) {
            return false;
        }

        phpr.send({
            url: phpr.webpath + 'index.php/Timecard/index/jsonSave/nodeId/' + phpr.currentProjectId +
                '/id/' + this.id,
            content: this.sendData
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    dijit.popup.close(this._timecardTooltipDialog);
                    this.updateData();
                    this.main.formDataChanged(this.dateObject, true);
                }
            }
        }));
    },

    deleteForm: function(id, event) {
        // Summary:
        //    Delete a booking
        // Description:
        //    Delete a bookinh and reload the views
        this.id = dijit.byId('timecardId').get('value');
        phpr.send({
            url: phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this.id
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    dijit.popup.close(this._timecardTooltipDialog);
                    this.updateData();
                    this.main.formDataChanged(this.dateObject, true);
                }
            }
        }));
    },

    submitFavoritesForm: function() {
        // Summary:
        //    Save the favorites projects
        // Description:
        //    Save the favorites projects
        this.sendData                = [];
        this.sendData['favorites[]'] = [];
        var _this                    = this;

        projectFavoritesTarget.getAllNodes().forEach(function(node) {
            var id = dojo.attr(node, 'dojoAttachPoint').replace(/favoritesTarget-/, "").replace(/favoritesSource-/, "");
            _this.sendData['favorites[]'].push(id);
        });

        if (this.sendData['favorites[]'].length === 0) {
            this.sendData['favorites[]'].push(0);
        }

        phpr.send({
            url:     phpr.webpath + 'index.php/Timecard/index/jsonFavoritesSave',
            content: this.sendData
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    phpr.DataStore.deleteData({url: this._favoritesUrl});
                    phpr.DataStore.requestData({url: this._favoritesUrl});
                }
            }
        }));
    },

    updateData: function() {
        // Summary:
        //    Delete the cache for all the views
        // Description:
        //    Delete the cache and reload the views
        this.id = 0;
        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.deleteData({url: this._bookUrl});
        this.drawDayView();
    },

    openManageFavorites: function() {
        // Summary:
        //    Function called on manageFavorites button click, to regulate the popup project's boxes height, and then
        //    open the Manage Favorites dialog
        if (this.main._contentWidget.dialogContent.domNode.innerHTML.replace(/\s/g, "") === "") {
            phpr.DataStore.addStore({url: this._favoritesUrl});
            phpr.DataStore.requestData({url: this._favoritesUrl, processData: dojo.hitch(this, function() {
                phpr.DataStore.addStore({url: this._url});
                phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
                    var favorites = phpr.DataStore.getData({url: this._favoritesUrl});
                    var meta = phpr.DataStore.getMetaData({url: this._url});
                    var range = meta[3].range || [];

                    // Get Favorites
                    var favoritesList = [];
                    for (var k in favorites) {
                        for (var j in range) {
                            if (range[j].id == favorites[k].id) {
                                favoritesList.push(range[j]);
                            }
                        }
                    }

                    // Get All Projects
                    var allProjects = [];
                    for (var j in range) {
                        var found = false;
                        for (var k in favorites) {
                            if (range[j].id == favorites[k].id) {
                                found = true;
                            }
                        }
                        if (!found) {
                            allProjects.push(range[j]);
                        }
                    }

                    // Make Dialog
                    this.createFavoritesDialog(allProjects, favoritesList);

                    this.finishDialog();
                })});
            })});
        } else {
            this.finishDialog();
        }
    },

    finishDialog: function() {
        // Summary:
        //    Show the dialog and resize it
        dijit.popup.close(this._timecardTooltipDialog);
        this.main._contentWidget.manageFavorites.show();

        // If there are no projects in any of the boxes, don't let it reduce its height so much
        if (projectFavoritesSource && projectFavoritesSource.getAllNodes().length === 0) {
            dojo.style('projectFavoritesSource', 'height', this._manFavBoxesHeight + 'px');
        } else {
            dojo.style('projectFavoritesSource', 'height', '');
        }
        if (projectFavoritesTarget && projectFavoritesTarget.getAllNodes().length === 0) {
            dojo.style('projectFavoritesTarget', 'height', this._manFavBoxesHeight + 'px');
        } else {
            dojo.style('projectFavoritesTarget', 'height', '');
        }
    },

    updateForm: function(date, start, end, project, notes) {
        // Summary:
        //    Fill the form with some data
        // Description:
        //    Fill the form with some data
        dijit.byId("startDatetime_forDate").set('displayedValue', phpr.date.getIsoDate(date));
        dijit.byId('startDatetime_forTime').set('displayedValue', start);
        dijit.byId('endTime').set('displayedValue', end);
        dijit.byId('projectId').set('value', project);
        dijit.byId('notes').set('value', notes);
        if (parseInt(this.id) > 0) {
            dojo.style(dojo.byId('deleteBookingButtonDiv'), 'display', 'inline');
        } else {
            dojo.style(dojo.byId('deleteBookingButtonDiv'), 'display', 'none');
        }
    },

    fillForm: function(id) {
        // Summary:
        //    Fill the form with the data from a saved item
        // Description:
        //    Fill the form with the data from a saved item
        this.id = id;
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/1/id/' + this.id;
        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
            var data = phpr.DataStore.getData({url: this._url});
            var endTime = data[0].endTime.substr(0, 5);
            var startTime = data[0].startDatetime.substr(11, 5);
            if (endTime === 0 || endTime === null) {
                var hour = parseInt(startTime) + 1;
                var endTime = phpr.date.getIsoTime(hour + '00');
            }

            var temp = startTime.split(':');

            var start = parseInt(temp[0]);
            if (start === 0) {
                var start = parseInt(temp[0].substr(1, 1));
            }
            var end = parseInt(temp[1]);

            var index;
            if (end >= 0 && end < 30) {
                index = start + ':00';
            } else if (end >= 30) {
                index = start + ':30';
            }

            this.drawFormView(this._dayView["buttonHours" + index].domNode, this.dateObject, startTime, endTime,
                data[0].projectId, data[0].notes);

            this.focusNote();
        })});
    },

    focusNote: function() {
        // Summary:
        //    Wait that the widget exists to focus it
        // Description:
        //    Wait that the widget exists to focus it
        if (!dojo.byId('notes')) {
            setTimeout(dojo.hitch(this, "focusNote"), 500);
        } else {
            dojo.byId('notes').focus();
        }
    },

    fillFormTime: function(index) {
        // Summary:
        //    Fill the form with the start and end time
        // Description:
        //    Fill the form with the start and end time
        this.id   = 0;
        var temp  = index.split(':');
        var start = parseInt(temp[0], 10);
        var end   = temp[1];

        var hour = start + 1;
        var end  = phpr.date.getIsoTime(hour + ':' + end);

        this.drawFormView(this._dayView["buttonHours" + index].domNode, this.dateObject, index, end, '', "\n");
    },

    _getNow: function() {
        // Summary:
        //    Return the current HH:mm
        // Description:
        //    Return the current HH:mm
        return phpr.date.getIsoTime(new Date());
    },

    _convertHourToPixels: function(time) {
        var hours   = (time.substr(0, 2) * this._hourHeight);
        var minutes = Math.floor((((time.substr(3, 2) / 60)) * this._hourHeight));

        return hours + minutes;
    },

    _convertAmountToPixels: function(time) {
        var hours   = (time.substr(0, 2) * this._hourHeight);
        var minutes = Math.floor((time.substr(3, 2) / 60) * this._hourHeight);

        return hours + minutes;
    }
});
