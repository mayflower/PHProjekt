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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.Form");

dojo.require("dijit.form.Button");
dojo.require("dijit.TooltipDialog");

dojo.declare("phpr.Timecard.Form", phpr.Default.System.Component, {
    dateObject: null,
    formdata: [],
    id: 0,
    sendData: {},
    _bookUrl: null,
    _date: null,
    _favoriteButton: null,
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
        this._url          = 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/1/id/' + this.id;

        this._dayView = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Timecard.template.dayView.html",
            templateData: {
                start: phpr.nls.get('Start'),
                end: phpr.nls.get('End'),
                project: phpr.nls.get('Project'),
                hours: phpr.nls.get('Hours'),
                note: phpr.nls.get('Note')
            }
        });

        this.main._contentWidget.dayView.set('content', this._dayView);

        this.setDate(date);
        this.loadView();
    },

    destroy: function() {
        this._destroyTemplateRenderer();
        this._formContent = null;
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

    _getHourDifferenceText: function(startDate, endDate) {
        var hours = (endDate - startDate) / 1000 / 60 / 60;
        var fhours = "" + Math.floor(hours);
        var minutes = "" + Math.floor((hours - fhours) * 60);
        time = (fhours.length === 1 ? "0" + fhours:fhours) + ":" + (minutes.length === 1 ? "0" + minutes : minutes);
        return time;
    },

    _parseTimeToDate: function(timeString) {
        var d = new Date(0);
        var time = timeString.match(/(\d+):(\d+):(\d+)/);
        d.setHours(parseInt(time[1], 10));
        d.setMinutes(parseInt(time[2], 10) || 0);
        return d;
    },

    drawDayView: function() {
        // Summary:
        //    Render the Day View
        // Description:
        //    Render the Day View
        this._bookUrl = 'index.php/' + phpr.module + '/index/jsonDayList/date/' + this._date;
        phpr.DataStore.addStore({url: this._bookUrl});
        phpr.DataStore.requestData({url: this._bookUrl, processData: dojo.hitch(this, function(reqData) {
            var data = reqData.data;
            // Clean "Day View"
            this.garbageCollector.collect("dayView");

            var accumulatedTime = 0;
            // Draw hours block
            for (var i in data) {
                var startDate = this._parseTimeToDate(data[i].startTime);
                var startTime = dojo.date.locale.format(startDate, { selector: "time" });
                var endTime = null;

                if (data[i].endTime !== null) {
                    var endDate = this._parseTimeToDate(data[i].endTime);
                    endTime = dojo.date.locale.format(endDate, { selector: "time" });
                    time = this._getHourDifferenceText(startDate, endDate);
                    accumulatedTime += endDate - startDate;
                } else {
                    endTime = "--:--";
                    time = "";
                }

                var projectName = data[i].display;
                if (data[i].projectId == 1) {
                    projectName = phpr.nls.get("Unassigned", "Timecard");
                }

                var entry = new phpr.Default.System.TemplateWrapper({
                    templateName: "phpr.Timecard.template.dayViewEntry.html",
                    templateData: {
                        starttime: startTime,
                        endtime: endTime,
                        hours: time,
                        projectname: projectName,
                        note: data[i].note
                    }
                });

                this.garbageCollector.addNode(entry, "dayView");

                this._dayView.list.appendChild(entry.domNode);
                this.garbageCollector.addEvent(
                    dojo.connect(
                        entry.domNode,
                        "onclick",
                        dojo.hitch(this, "drawFormView", data[i].id)
                    ),
                    "dayView"
                );
            }

            var hourdiff = this._getHourDifferenceText(new Date(0), new Date(accumulatedTime));
            this._dayView.footer.innerHTML = phpr.nls.get("Sum") + ": " + hourdiff;
            this.drawFormView(0);
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
                disabled:  false,
                onClick: function() {
                    phpr.pageManager.modifyCurrentState({
                        moduleName: "Setting",
                        action: "Timecard",
                        projectId: undefined,
                        id: undefined
                    });
                }
            };

            this._favoriteButton = new dijit.form.Button(params);
            phpr.viewManager.getView().buttonRow.domNode.appendChild(this._favoriteButton.domNode);
            this.garbageCollector.addNode(this._favoriteButton);
        }
    },

    drawFormView: function(id) {
        // Summary:
        //    Render the form and the favorites
        // Description:
        //    Render the form and the favorites
        id = id || 0;

        this._url = 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/1/id/' + id;

        phpr.DataStore.addStore({url: this._url});
        var dlist = new dojo.DeferredList([
            phpr.DataStore.requestData({url: this._url}),
            this.main._store.getMergedFavoriteProjects()
        ]);

        dlist.addCallback(dojo.hitch(this, function(reqData) {
            if (this._formContent) {
                this._formContent.destroyRecursive();
            }
            var data = reqData[0][1].data[0];
            var meta = reqData[0][1].metaData;
            var range = reqData[1][1];

            this._templateRenderer = new phpr.Default.Field();

            // Init formdata
            var formData = [];
            // startDatetime
            var currentDateTime = phpr.date.getIsoDatetime(this.dateObject, phpr.date.getIsoTime(new Date()));
            formData.push(
                this._templateRenderer.datetimeRender(
                    meta[0].label,
                    meta[0].key,
                    id === 0 ? currentDateTime : data[meta[0].key],
                    meta[0].required,
                    false,
                    meta[0].hint
                )
            );
            // endTime
            formData.push(this._templateRenderer.timeRender(meta[1].label, meta[1].key, data[meta[1].key],
                    meta[1].required, false, meta[1].hint));

            formData.push(this._templateRenderer.selectRender(range, meta[3].label, meta[3].key, data[meta[3].key],
                    meta[3].required, false, meta[3].hint));
            // notes
            formData.push(this._templateRenderer.textAreaRender(meta[4].label, meta[4].key, data[meta[4].key],
                        meta[4].required, false, meta[4].hint));

            // timecardId
            formData.push(this._templateRenderer.hiddenFieldRender('', 'timecardId', id, true, false));

            this._formContent = new phpr.Default.System.TemplateWrapper({
                templateName: "phpr.Timecard.template.formView.html",
                templateData: {
                    saveText: phpr.nls.get('Save'),
                    deleteText: phpr.nls.get('Delete'),
                    newText: phpr.nls.get('New'),
                    deleteDisplay: id > 0 ? "inline" : "none"
                }
            });

            this.garbageCollector.addNode(this._formContent);

            for (var i in formData) {
                dojo.place(formData[i].domNode, this._formContent.formBottom, 'before');
            }

            this._dayView.form.set('content', this._formContent);

            this.garbageCollector.addEvent(
                dojo.connect(this._formContent.saveBookingButton, "onClick", dojo.hitch(this, "submitForm", id))
            );

            this.garbageCollector.addEvent(
                dojo.connect(this._formContent.deleteBookingButton, "onClick", dojo.hitch(this, function() {
                    phpr.confirmDialog(dojo.hitch(this, "deleteForm", id), phpr.nls.get('Are you sure you want to delete?'));
                }))
            );

            this.garbageCollector.addEvent(
                dojo.connect(this._formContent.newBookingButton, "onClick", dojo.hitch(this, function() {
                    this.drawFormView(0);
                }))
            );

            formData[2].fieldNode.focus();
        }));
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
            this._formContent.bookingForm.validate();
            return false;
        }

        return true;
    },

    submitForm: function(id) {
        // Summary:
        //    Save the booking form
        // Description:
        //    Save the booking form and reload the views
        this.sendData = {};
        this.sendData = dojo.mixin(this.sendData, this._formContent.bookingForm.get('value'));
        if (!this.prepareSubmission()) {
            return false;
        }

        phpr.send({
            url: 'index.php/Timecard/index/jsonSave/nodeId/1/id/' + id,
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

    deleteForm: function(id) {
        // Summary:
        //    Delete a booking
        // Description:
        //    Delete a bookinh and reload the views
        phpr.send({
            url: 'index.php/' + phpr.module + '/index/jsonDelete/id/' + id
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

    updateData: function() {
        // Summary:
        //    Delete the cache for all the views
        // Description:
        //    Delete the cache and reload the views
        this.id = 0;
        phpr.DataStore.deleteData({url: this._bookUrl});
        phpr.DataStore.deleteData({url: this._url});
        this.drawDayView();
    }
});
