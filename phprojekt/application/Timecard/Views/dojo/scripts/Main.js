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

dojo.provide("phpr.Timecard.Main");

dojo.declare("phpr.Timecard.BookingStore", null, {
    _date: null,
    _url: null,
    _detailsUrl: null,
    _projectRange: null,
    _loading: false,
    _unassignedProjectId: 1,

    constructor: function(date) {
        this._date = date;
        this._setUrls();
    },

    _setUrls: function() {
        this._url = phpr.webpath + 'index.php/Timecard/index/jsonDayList/date/' + phpr.date.getIsoDate(this._date);
        this._detailsUrl = phpr.webpath + 'index.php/Timecard/index/jsonDetail/nodeId/1/id/0';
    },

    _onDataLoaded: function() {
        this._data = phpr.DataStore.getData({url: this._url});
        this._metaData = phpr.DataStore.getMetaData({url: this._detailsUrl});
        this._runningBooking = null;
        var l = this._data.length;

        this._projectRange = this._getProjectRange(this._metaData);

        for (var i = 0; i < l; i++) {
            if (this._data[i].endTime === null) {
                this._runningBooking = this._data[i];
                break;
            }
        }

        this._stopLoading();
        this.onChange();
    },

    _getProjectRange: function(metaData) {
        var range = dojo.clone(metaData[3].range);

        var l = range.length;
        for (var i = 0; i < l; i++) {
            if (range[i].id == this._unassignedProjectId) {
                range[i].name = phpr.nls.get("Unassigned", "Timecard");
                break;
            }
        }

        return range;
    },

    _updateData: function() {
        if (!this.isLoading()) {
            this._startLoading();
            this._setUrls();

            phpr.DataStore.deleteData({url: this._url});
            phpr.DataStore.deleteData({url: this._detailsUrl});

            phpr.DataStore.addStore({url: this._url});
            phpr.DataStore.addStore({url: this._detailsUrl});

            var dlist = new dojo.DeferredList([
                phpr.DataStore.requestData({url: this._url}),
                phpr.DataStore.requestData({url: this._detailsUrl})
            ]);

            dlist.addCallback(dojo.hitch(this, "_onDataLoaded"));
        }
    },

    _startLoading: function() {
        this._loading = true;
        this.onLoadingStart();
    },

    _stopLoading: function() {
        this._loading = false;
        this.onLoadingStop();
    },

    hasRunningBooking: function() {
        return this._runningBooking !== null;
    },

    getRunningBooking: function() {
        return this._runningBooking;
    },

    getProjectRange: function() {
        return this._projectRange;
    },

    startWorking: function(projectId, notes) {
        var data = {
            startDatetime: phpr.date.getIsoDate(this._date) + " " + phpr.date.getIsoTime(new Date()),
            projectId: projectId || this._unassignedProjectId,
            notes: notes || ""
        };

        phpr.send({
            url: phpr.webpath + 'index.php/Timecard/index/jsonSave/nodeId/1/id/0',
            content: data
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.dataChanged();
                    this.onWorkingStart();
                }
            }
        }));
    },

    stopWorking: function(projectId, notes) {
        var data = {
            startDatetime: phpr.date.getIsoDate(this._date) + " " + phpr.date.getIsoTime(this._runningBooking.startTime),
            endTime: phpr.date.getIsoTime(new Date()),
            projectId: projectId || this._runningBooking.projectId || this._unassignedProjectId,
            timecardId: this._runningBooking.id,
            notes: notes || ""
        };

        phpr.send({
            url: phpr.webpath + 'index.php/Timecard/index/jsonSave/nodeId/1/id/' + this._runningBooking.id,
            content: data
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.dataChanged();
                    this.onWorkingStop();
                }
            }
        }));
    },

    dataChanged: function() {
        this._updateData();
    },

    setDate: function(date) {
        this._date = date;
        this._setUrls();
        this.dataChanged();
    },

    isLoading: function() {
        return this._loading === true;
    },

    onWorkingStart: function() { },
    onWorkingStop: function() { },
    onLoadingStart: function() { },
    onLoadingStop: function() { },
    onChange: function() { }
});

dojo.declare("phpr.Timecard.Main", phpr.Default.Main, {
    _date: new Date(),
    _contentWidget: null,
    _bookingStore: null,
    startStopBar: null,

    constructor: function() {
        this.module = 'Timecard';
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Timecard.Grid;
        this.formWidget = phpr.Timecard.Form;
        this._bookingStore = new phpr.Timecard.BookingStore(this._date);

        dojo.connect(this._bookingStore, "onChange", this, "_dataChanged");
    },

    renderTemplate: function() {
        // Summary:
        //   Custom renderTemplate for timecard
        var view = phpr.viewManager.useDefaultView({blank: true}).clear();
        this._contentWidget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Timecard.template.mainContent.html",
            templateData: {
                manageFavoritesText: phpr.nls.get('Manage project list'),
                monthTxt:            phpr.date.getLongTranslateMonth(this._date.getMonth())
            }
        });
        view.centerMainContent.set('content', this._contentWidget);
        this.garbageCollector.addNode(this._contentWidget);

        // manageFavorites opens a dialog which places itself outside of the regular dom, so we need to clean it up
        // manually
        this.garbageCollector.addNode('manageFavorites');
    },

    setWidgets: function() {
        // Summary:
        //   Custom setWidgets for timecard
        phpr.tree.loadTree();
        this._bookingStore.dataChanged();
        this.grid = new this.gridWidget(this, this._date);
        this.form = new this.formWidget(this, this._date);
        this.startStopBar = new phpr.Timecard.StartStopBar({
            container: this._contentWidget.startStopButtonRow,
            bookingStore: this._bookingStore
        });
        this.garbageCollector.addObject(this.startStopBar);
    },

    _dataChanged: function() {
        if (this.form) {
            this.form.updateData();
        }

        if (this.grid) {
            this.grid.reload(this._date, true);
        }
    },

    formDataChanged: function(newDate, forceReload) {
        this._bookingStore.dataChanged();
    },

    setSubGlobalModulesNavigation: function(currentModule) {
    },

    changeDate: function(date) {
        // summary:
        //    Update the date and reload the views
        // description:
        //    Update the date and reload the views
        this._date = date;

        this.form.setDate(date);
        this.form.updateData();
        this.form.drawDayView();

        this.grid.reload(date);
    }
});
