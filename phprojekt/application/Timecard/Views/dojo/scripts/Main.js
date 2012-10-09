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

dojo.provide("phpr.Timecard.Main");

dojo.declare("phpr.Timecard.Store", null, {
    _hasData: false,
    _date: null,
    _url: null,
    _detailsUrl: null,
    _projectRange: null,
    _loading: false,
    _unassignedProjectId: 1,
    _favoritesUrl: null,
    _data: null,
    _metaData: null,
    _favoritesData: null,
    _mergedFavorites: null,
    _dlist: null,
    _updateListener: null,

    constructor: function(date) {
        this._date = date;

        this._updateListener = dojo.subscribe("Project.updateCacheData", this, "dataChanged");
        this._updateListener = dojo.subscribe("phpr.moduleSettingsChanged", this, "_settingsChanged");
    },

    destory: function() {
        dojo.unsubscribe(this._updateListener);
    },

    _settingsChanged: function(module) {
        if (module === "Timecard") {
            this.dataChanged();
        }
    },

    _setUrls: function() {
        this._favoritesUrl = 'index.php/Timecard/index/jsonGetFavoritesProjects';
        this._url = 'index.php/Timecard/index/jsonGetRunningBookings/' +
                    'year/' + this._date.getFullYear() +
                    '/month/' + (this._date.getMonth() + 1) +
                    '/date/' + this._date.getDate()
                                ;
        this._detailsUrl = 'index.php/Timecard/index/jsonDetail/nodeId/1/id/0';
    },

    _onDataLoaded: function(data) {
        this._data = data[0][1].data;
        this._metaData = data[1][1].metaData;
        this._favoritesData = data[2][1].data;
        this._runningBooking = null;

        this._projectRange = this._getProjectRange(this._metaData);
        this._computeMergedFavorites();

        if (this._data.length !== 0) {
            this._runningBooking = this._data;
        }

        this._dlist = null;
        this._hasData = true;
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

    /*
     * This function merges the project list with the favorites.
     * the resulting list will be the same as the project list, but the favorites will be placed at the top of the list
     */
    _computeMergedFavorites: function() {
        if (this._metaData && this._metaData[3].range && this._favoritesData) {
            // projectId
            var favorites = this._favoritesData;
            var range = dojo.clone(this._projectRange);
            for (var i in favorites) {
                var id = parseInt(favorites[i].id, 10);
                if (id > 0) {
                    for (var j in range) {
                        if (range[j].id == id) {
                            range.slice(j, 1);
                            break;
                        }
                    }
                    range.unshift({'id': parseInt(favorites[i].id, 10), 'name': favorites[i].name});
                }
            }
            this._mergedFavorites = range;
            return range;
        } else {
            return null;
        }
    },

    _updateData: function() {
        if (!this.isLoading()) {
            this._setUrls();
            this._cleanFavoritesData();

            phpr.DataStore.deleteData({url: this._url});

            phpr.DataStore.addStore({url: this._url});
            phpr.DataStore.addStore({url: this._detailsUrl});
            return this._requestData();
        }
    },

    _requestData: function() {
        if (!this.isLoading()) {
            this._startLoading();
            this._dlist = new dojo.DeferredList([
                phpr.DataStore.requestData({url: this._url}),
                phpr.DataStore.requestData({url: this._detailsUrl}),
                phpr.DataStore.requestData({url: this._favoritesUrl})
            ]);

            this._dlist.addCallback(dojo.hitch(this, "_onDataLoaded"));
            return this._dlist;
        }
    },

    _cleanFavoritesData: function() {
        if (!this.isLoading()) {
            phpr.DataStore.deleteData({url: this._favoritesUrl});
            phpr.DataStore.addStore({url: this._favoritesUrl});
        }
    },

    _updateFavoritesData: function() {
        this._cleanFavoritesData();
        this._requestData();
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
        var cb = new dojo.Deferred();
        if (this._hasData === true) {
            cb.callback(this._projectRange);
        } else {
            this._dlist.addCallback(dojo.hitch(this, function() {
                cb.callback(this._projectRange);
            }));
        }
        return cb;
    },

    startWorking: function(projectId, notes) {
        var data = {
            startDatetime: phpr.date.getIsoDate(this._date) + " " + phpr.date.getIsoTime(new Date()),
            projectId: projectId || this._unassignedProjectId,
            notes: notes || ""
        };

        phpr.send({
            url: 'index.php/Timecard/index/jsonSave/nodeId/1/id/0',
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
        if (!this.hasRunningBooking()) {
            throw new Error("no running booking");
        }

        var data = {
            startDatetime: phpr.date.getIsoDate(this._date) + " " + phpr.date.getIsoTime(this._runningBooking.startTime),
            endTime: phpr.date.getIsoTime(new Date()),
            projectId: projectId || this.getLastProjectId(),
            notes: notes || this._runningBooking.note || ""
        };

        phpr.send({
            url: 'index.php/Timecard/index/jsonSave/nodeId/1/id/' + this._runningBooking.id,
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

    setFavoriteProjects: function(projects) {
        if (!dojo.isArray(projects)) {
            throw new Error("Invalid project list");
        }

        if (projects.length === 0) {
            projects.push(0);
        }

        var sendData = {
            'favorites[]': projects
        };

        phpr.send({
            url:     'index.php/Timecard/index/jsonFavoritesSave',
            content: sendData
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this._updateFavoritesData();
                }
            }
        }));
    },

    getLastProjectId: function() {
        if (this.hasRunningBooking()) {
            return parseInt(this._runningBooking.projectId, 10);
        } else {
            return this._unassignedProjectId;
        }
    },

    getMergedFavoriteProjects: function() {
        var cb = new dojo.Deferred();

        if (this._hasData === true) {
            cb.callback(this._mergedFavorites);
        } else {
            this._dlist.addCallback(dojo.hitch(this, function() {
                cb.callback(this._mergedFavorites);
            }));
        }
        return cb;
    },

    getFavoriteProjects: function() {
        var cb = new dojo.Deferred();
        if (this._hasData === true) {
            cb.callback(this._favoritesData);
        } else {
            this._dlist.addCallback(dojo.hitch(this, function() {
                cb.callback(this._favoritesData);
            }));
        }
        return cb;
    },

    dataChanged: function() {
        this._updateData();
    },

    setDate: function(date) {
        this._date = date;
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
    _menuCollector: null,
    _store: null,

    constructor: function() {
        this.module = 'Timecard';
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Timecard.Grid;
        this.formWidget = phpr.Timecard.Form;
        this._store = new phpr.Timecard.Store(this._date);

        this._menuCollector = new phpr.Default.System.GarbageCollector();

        dojo.subscribe("Timecard.changeDate", this, "changeDate");
        dojo.subscribe("phpr.dateChanged", this, "_systemDateChanged");
        dojo.connect(this._store, "onChange", this, "_dataChanged");
    },

    _systemDateChanged: function() {
        this._store.setDate(new Date());
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
        this._store.dataChanged();

        this.grid = new this.gridWidget(this, this._date);
        this.form = new this.formWidget(this, this._date);
        this.setTimecardCaldavClientButton();
    },

    _dataChanged: function() {
        if (this.form) {
            this.form.updateData();
        }

        if (this.grid) {
            this.grid.reload(this._date, true);
        }

        this._updateMenuButton();
    },

    _updateMenuButton: function() {
        var that = this;
        if (this._menuButton && this._menuButton.dropDown) {
            this._menuCollector.collect();
            this._menuButton.dropDown.destroyDescendants();
            var button;

            if (this._store.hasRunningBooking()) {
                this._store.getMergedFavoriteProjects().then(dojo.hitch(this, function(data) {
                    var range = data;
                    var l = range.length;
                    var lastProjectName = "";
                    var lastProjectId = this._store.getLastProjectId();

                    for (var i = 0; i < l; i++) {
                        button = new dijit.MenuItem({
                            label: range[i].name,
                            onClick: dojo.hitch(this._store, function(id) {
                                this.stopWorking(id);
                            }, range[i].id)
                        });

                        if (range[i].id === lastProjectId) {
                            lastProjectName = range[i].name;
                        }

                        this._menuCollector.addNode(button);
                        this._menuButton.dropDown.addChild(button);
                    }

                    button = new dijit.MenuItem({
                        label: "Stop (" + lastProjectName + ")",
                        onClick: dojo.hitch(this._store, function() {
                            this.stopWorking();
                        })
                    });

                    this._menuCollector.addNode(button);
                    this._menuButton.dropDown.addChild(button, 0);

                    dojo.addClass(this._menuButton.focusNode, "runningBooking");
                }));
            } else {
                this._menuCollector.addEvent(
                    dojo.connect(this._menuButton.dropDown, "onOpen", this._store,
                        function (evt) {
                            if (!this.hasRunningBooking()) {
                                dijit.popup.close(that._menuButton.dropDown.currentPopup);
                                this.startWorking();
                            }
                        }));
                dojo.removeClass(this._menuButton.focusNode, "runningBooking");
            }
            button = null;
        }
    },

    formDataChanged: function(newDate, forceReload) {
        this._store.dataChanged();
    },

    setSubGlobalModulesNavigation: function(currentModule) {
    },

    getGlobalModuleNavigationButton: function(label) {
        var moduleName = this.module;
        var buttonContainer = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Timecard.template.menuButton.html",
            templateData: { label: label }
        });

        var button = buttonContainer.menuButton;
        this._menuButton = button;

        dojo.connect(button, "onClick", function() {
            phpr.currentProjectId = phpr.rootProjectId;
            phpr.pageManager.modifyCurrentState(
                dojo.mixin(dojo.clone(this._emptyState), { moduleName: moduleName }));
        });

        setTimeout(
            dojo.hitch(this, function() {
                this._store.dataChanged();
            }),
            15
        );
        return button;
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
    },

    setTimecardCaldavClientButton: function() {
        // Summary:
        //    Set the timecardCaldavClient button
        // Description:
        //    Set the timecardCaldavClient button
        this.garbageCollector.collect('timecardCaldavClient');

        var prefix = phpr.getAbsoluteUrl('index.php/Timecard/caldav/index/'),
            url = prefix + 'calendars/' + phpr.config.currentUserName + '/default/',
            iosUrl = prefix + 'principals/' + phpr.config.currentUserName + '/',
            params = {
                label: 'Timecard Caldav Client',
                showLabel: true,
                baseClass: 'positive',
                disabled: false
            },
            timecardCaldavClientButton = new dijit.form.Button(params);

        phpr.viewManager.getView().buttonRow.domNode.appendChild(timecardCaldavClientButton.domNode);

        this.garbageCollector.addNode(timecardCaldavClientButton, 'timecardCaldavClient');
        this.garbageCollector.addEvent(
            dojo.connect(
                timecardCaldavClientButton,
                'onClick',
                dojo.hitch(this, 'showTimecardCaldavClientData', url, iosUrl)
            ),
            'timecardCaldavClient'
        );
    },

    showTimecardCaldavClientData: function(url, iosUrl) {
        var content = phpr.fillTemplate(
            'phpr.Calendar2.template.caldavView.html',
            {
                headline: 'Timecard Caldav Client',
                normalLabel: phpr.nls.get('CalDav url', 'Calendar2'),
                iosLabel: phpr.nls.get('CalDav url for Apple software', 'Calendar2'),
                noticeLabel: phpr.nls.get('Notice', 'Calendar2'),
                notice: phpr.nls.get('Please pay attention to the trailing slash, it is important', 'Calendar2'),
                normalUrl: url,
                iosUrl: iosUrl
            }
        );

        //draggable = false must be set because otherwise the dialog can not be closed on the ipad
        //bug: http://bugs.dojotoolkit.org/ticket/13488
        var dialog = new dijit.Dialog({
            content: content,
            draggable: false
        });

        dialog.show();
        this.garbageCollector.addNode(dialog, 'timecardCaldavClient');
    }
});
